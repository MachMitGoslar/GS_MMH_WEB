<?php

// Tests never commit: git-content would otherwise commit into whatever git
// repository encloses the content root (the main repository for fixtures).
return [
    'thathoff.git-content.disable' => true,
];
