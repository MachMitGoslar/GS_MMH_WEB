<?php

/**
 * Site Helper Functions
 *
 * This file contains site-specific helper functions that support
 * the MachMit!Haus website functionality.
 */

/**
 * Get the color class name for a project status
 *
 * Maps German project status values to CSS color class names
 * for consistent styling across the site.
 *
 * @param string $status The project status in German
 * @return string The corresponding CSS color class name
 */
function getProjectStatusColor(string $status): string
{
    switch ($status) {
        case "in Planung":
            return "planning";
        case "in Vorbereitung":
            return "preparing";
        case "aktiv":
            return "active";
        case "in Auswertung":
            return "review";
        case "abgeschlossen":
            return "done";
        default:
            return "false";
    }
}
