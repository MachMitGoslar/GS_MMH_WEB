# Runbook: `content/` vom Submodul zum eigenständigen Repo

Teil von Phase 3 in [CLEANUP.md](CLEANUP.md). Dieses Dokument wird abgehakt, sobald eine Instanz umgestellt ist (Datum und Name eintragen).

## Ausgangslage
- `content/` ist ein Submodul von `https://github.com/MachMitGoslar/web_content`, der Pointer im Hauptrepo ist veraltet (`1e85a2a`).
- Die Pipeline checkt Submodule rekursiv aus, **kopiert `content/` aber nicht**. Auf den Servern liegt der Content bereits, git-content committet dort bei jeder Panel-Aktion.
- Auf Production gibt es redaktionelle Änderungen, die nicht im Remote sind:
  - **(A)** lokal committete, nicht gepushte Commits (git-content pusht nicht, `push` ist aus)
  - **(B)** Änderungen **außerhalb** dessen, was git-content committet (manuell geänderte oder hochgeladene Dateien): Working-Tree-Änderungen und untracked Dateien, die händisch committet werden müssen

## Ziel
`content/` ist auf jeder Instanz ein normales, eigenständiges Git-Repo (`content/.git` ist ein **Verzeichnis**) auf dem passenden **Branch** (`production` bzw. `staging`), und das Hauptrepo kennt `content/` nicht mehr.

## Regeln, damit nichts verloren geht
1. **Reihenfolge einhalten:** Pipeline → Instanz umwandeln → Hauptrepo. Nie umgekehrt.
2. **Nie** `git submodule deinit content` auf einem Server ausführen. Das leert das Arbeitsverzeichnis.
3. **Nie** `git submodule update` für `content` ausführen, solange der Eintrag in `.gitmodules` steht. Es setzt auf den veralteten Pointer zurück.
4. **Nie** `git push --force` nach `web_content`. Bei Abweichungen immer erst auf einen Backup-Branch pushen.
5. **Erst Backup, dann alles andere.** Pro Instanz, jedes Mal.
6. Staging zuerst, Production erst, wenn Staging vollständig durchgelaufen ist.

---

## Schritt 1: Pipeline anpassen (vor allem anderen)
Ort: externe Deploy-Pipeline (nicht im Repo).

- [ ] Rekursiven Checkout ersetzen. Statt `git submodule update --init --recursive`:
  ```bash
  git submodule update --init -- \
    site/plugins/gs-mmh-web-plugin \
    site/plugins/gs-mmh-signage-plugin \
    site/plugins/git-content
  ```
- [ ] Prüfen, dass die Pipeline `content/` weder kopiert, synchronisiert noch löscht (kein `rsync --delete`, kein `rm -rf` auf `content/`).
- [ ] Testlauf auf Staging: `content/` ist danach unverändert (Dateianzahl und `git -C content status --porcelain` vergleichen).
- [ ] Pipeline-Datum und Verantwortliche eintragen: ____________

Warum zuerst: Solange die Pipeline `content` mit auscheckt und `content/.git` später ein Verzeichnis ist, würde ein `submodule update` auf den alten Pointer `1e85a2a` zurücksetzen.

---

## Schritt 2: Pro Instanz sichern und Bestand aufnehmen
Variablen: `REPO` = Pfad des Hauptrepos auf dem Server, `DATE=$(date +%F)`.

- [ ] Backup des Arbeitsverzeichnisses **und** des Git-Verzeichnisses:
  ```bash
  cd "$REPO"
  DATE=$(date +%F)
  cp -a content ../content-backup-$DATE
  cp -a .git/modules/content ../content-gitdir-backup-$DATE
  ```
- [ ] Bestand festhalten (für den Vorher/Nachher-Vergleich):
  ```bash
  git -C content status --porcelain > ../content-status-before-$DATE.txt
  git -C content log --oneline -1 > ../content-head-before-$DATE.txt
  find content -path content/.git -prune -o -type f -print | wc -l   # Dateianzahl notieren
  ```
- [ ] **Losgelöster HEAD prüfen** (Submodule stehen oft auf einem Commit statt auf einem Branch):
  ```bash
  git -C content branch --show-current   # leer = detached HEAD
  ```
  Ist er leer, hängen **alle** Commits von git-content an keinem Branch und gehen beim nächsten Branchwechsel oder `gc` verloren. Dann sofort sichern:
  ```bash
  git -C content branch backup/detached-$DATE      # hält die Commits fest
  ```
- [ ] Remote und Branch klären (Production: `production`, Staging: `staging`):
  ```bash
  git -C content remote -v
  git -C content fetch origin
  git -C content branch -a
  ```

## Schritt 3: Unpushed Commits (A) und manuelle Änderungen (B) sichern
- [ ] Zeigen, was nicht im Remote ist (`<branch>` = `production` bzw. `staging`):
  ```bash
  git -C content log --oneline origin/<branch>..HEAD     # (A) lokale, nicht gepushte Commits
  git -C content status --short                          # (B) geänderte und untracked Dateien
  ```
- [ ] **(B) von Hand committen.** Erst ansehen, dann gezielt hinzufügen, nicht blind `add -A`:
  ```bash
  git -C content status --short
  git -C content add -- <pfade>        # Redaktionsinhalt: Texte, Bilder, Dateien
  git -C content commit -m "content: Redaktionelle Änderungen vom <datum> (manuell)"
  ```
  Nicht committen: Formulareinsendungen, Sessions, Caches, Zugangsdaten (siehe `content/.gitignore`). Bei Unklarheit nachfragen.
- [ ] Alles auf einen **Backup-Branch** im Remote pushen. Nie auf `production`/`staging`, solange unklar ist, ob das Remote weitergegangen ist:
  ```bash
  git -C content push origin HEAD:refs/heads/backup/<instanz>-$DATE
  ```
- [ ] Abgleich mit dem Branch-Remote:
  ```bash
  git -C content status -sb          # zeigt: ahead N / behind M
  ```
  - **nur ahead:** Commits dürfen regulär auf `<branch>` gepusht werden (oder bewusst per Pull Request übernehmen).
  - **ahead und behind (abgewichen):** nicht pushen. Backup-Branch bleibt bestehen, Zusammenführung (Merge) wird gemeinsam entschieden. Der Server bleibt zunächst auf seinem Stand.
- [ ] Entscheidung festhalten: ____________ (gepusht auf `<branch>` / nur Backup-Branch / Merge geplant)

## Schritt 4: Auf den Branch wechseln (nur wenn HEAD losgelöst war)
- [ ] Branch auf dem aktuellen Commit anlegen, ohne das Arbeitsverzeichnis zu verändern:
  ```bash
  git -C content switch -c <branch>          # legt den Branch auf HEAD an
  git -C content branch --set-upstream-to=origin/<branch> <branch>
  ```
  Existiert lokal schon ein Branch dieses Namens: nicht überschreiben, sondern Namen mit Suffix wählen und mit dem Team klären.

## Schritt 5: Submodul zum eigenständigen Repo machen
Das Arbeitsverzeichnis bleibt dabei unangetastet.

- [ ] Die Git-Daten aus dem Hauptrepo in `content/` verschieben:
  ```bash
  cd "$REPO"
  rm content/.git                       # nur die Verweisdatei "gitdir: ../.git/modules/content"
  mv .git/modules/content content/.git
  git config --file content/.git/config --unset core.worktree
  ```
  **Wichtig:** genau so, mit `--file`. `git -C content config --unset core.worktree` scheitert mit
  `fatal: cannot chdir to '../../../content'`, weil git vor dem Befehl versucht, in das noch falsch
  aufgelöste Arbeitsverzeichnis zu wechseln. Bleibt `core.worktree` stehen, ist **jeder** git-Befehl in
  `content/` kaputt, auch git-content im Panel. Die Arbeitsdateien und der Verlauf sind dabei nicht
  beschädigt; die Zeile mit `git config --file content/.git/config --unset core.worktree` entfernen genügt.
- [ ] Submodul-Konfiguration entfernen (ohne `deinit`!):
  ```bash
  git config --remove-section submodule.content 2>/dev/null || true
  ```
- [ ] Prüfen:
  ```bash
  git config --file content/.git/config --get core.worktree   # darf nichts ausgeben
  ls -d content/.git                                   # ist ein Verzeichnis
  git -C content status --porcelain | diff - ../content-status-before-$DATE.txt
  git -C content rev-parse --show-toplevel             # .../content
  find content -path content/.git -prune -o -type f -print | wc -l   # gleiche Dateianzahl wie vorher
  ```
  Es dürfen keine Unterschiede zum Stand aus Schritt 2 auftauchen (außer dem in Schritt 3 Committeten).
- [ ] Prüfen, dass git-content weiter funktioniert: im Panel eine unwichtige Seite speichern und `git -C content log -1` ansehen (neuer Commit auf `<branch>`).

Rollback, falls etwas nicht stimmt:
```bash
cd "$REPO"
rm -rf content
cp -a ../content-backup-$DATE content
cp -a ../content-gitdir-backup-$DATE .git/modules/content
```

## Schritt 6: Hauptrepo ändern (erst wenn alle Instanzen umgestellt sind)
Im Repo (PR):
- [ ] `git rm --cached content`
- [ ] Abschnitt `[submodule "content"]` aus `.gitmodules` entfernen
- [ ] In `.gitignore` bleibt `/content/`; die toten DreamForm-Regeln (`/content/forms/**`, `!/content/forms/*/form.txt`) entfernen
- [ ] README (Zeile „`git clone --recurse-submodules`“ und „content/ (gitignored)“) und `DEVELOPMENT_SETUP.md`: Entwickler holen den Content mit
  ```bash
  git clone https://github.com/MachMitGoslar/web_content content
  git -C content switch staging
  ```
- [ ] Der CI-Job `visual` und `ci-visual.sh` brauchen keine Änderung (sie holen den Content selbst)

Beim Deploy dieses PRs auf einer Instanz:
- [ ] Vorher `content/.git` ist ein Verzeichnis (Schritt 5 erledigt)
- [ ] Nach dem Deploy: `content/` ist unverändert vorhanden, `git status` im Hauptrepo zeigt `content/` nicht mehr, `git submodule status` nennt `content` nicht mehr

## Schritt 7: Danach
- [ ] git-content: `MMH_GIT_CONTENT_SECRET` setzen, Cron-Aufrufe um `?secret=` ergänzen (gehört zu Phase 1)
- [ ] Staging-Config anlegen (siehe Phase 3 in CLEANUP.md)
- [ ] Backups (`../content-backup-*`, `../content-gitdir-backup-*`) erst löschen, wenn eine Woche lang alles unauffällig lief
- [ ] Backup-Branches in `web_content` (`backup/*`) aufräumen, sobald alles übernommen ist

## Pro Instanz abhaken

| Instanz | Pipeline | Backup | A gesichert | B committet | Branch | umgewandelt | Hauptrepo deployt | Datum |
|---|---|---|---|---|---|---|---|---|
| Staging | | | | | | | | |
| Production | | | | | | | | |
| Dev (lokal) | n/a | | | | | | | |

Dev-Rechner: dieselben Schritte 2 bis 5 oder einfach neu klonen (`git clone ... content`, vorher lokale Änderungen sichern).
