<?php
/**
 * View Helper Functions
 */

// Only define these functions if they haven't been defined already
// This prevents "function already declared" errors if templates/components.php was included
if (!function_exists('dashboardCard')) {
/**
 * Generate a dashboard card
 *
 * @param string $title Card title
 * @param string $value Card value
 * @param string $icon Card icon (font awesome)
 * @param string $color Card color (primary, success, warning, danger)
 * @param string $url Link URL
 * @param string $linkText Link text
 * @return string HTML for the card
 */
function dashboardCard($title, $value, $icon, $color, $url, $linkText) {
    $html = '<div class="card bg-' . $color . ' text-white mb-4">';
    $html .= '<div class="card-body">';
    $html .= '<div class="d-flex justify-content-between align-items-center">';
    $html .= '<div>';
    $html .= '<div class="text-xs font-weight-bold text-uppercase mb-1">' . $title . '</div>';
    $html .= '<div class="h5 mb-0 font-weight-bold">' . $value . '</div>';
    $html .= '</div>';
    $html .= '<div class="col-auto">';
    $html .= '<i class="fas fa-' . $icon . ' fa-2x text-white-300"></i>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="card-footer d-flex align-items-center justify-content-between">';
    $html .= '<a class="small text-white stretched-link" href="' . $url . '">' . $linkText . '</a>';
    $html .= '<div class="small text-white"><i class="fas fa-angle-right"></i></div>';
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}
}

if (!function_exists('contentCard')) {
/**
 * Generate a content card
 *
 * @param string $header Card header
 * @param string $content Card content
 * @param string $headerClass Header CSS class
 * @param string $footer Card footer (optional)
 * @return string HTML for the card
 */
function contentCard($header, $content, $headerClass = '', $footer = '') {
    $html = '<div class="card mb-4">';
    $html .= '<div class="card-header ' . $headerClass . '">' . $header . '</div>';
    $html .= '<div class="card-body">' . $content . '</div>';
    
    if (!empty($footer)) {
        $html .= '<div class="card-footer">' . $footer . '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}
}

if (!function_exists('dataTable')) {
/**
 * Generate a data table
 *
 * @param array $headers Table headers
 * @param array $rows Table rows
 * @param string $classes Additional CSS classes
 * @return string HTML for the table
 */
function dataTable($headers, $rows, $classes = '') {
    $html = '<div class="table-responsive">';
    $html .= '<table class="table ' . $classes . '">';
    
    // Headers
    $html .= '<thead><tr>';
    foreach ($headers as $header) {
        $html .= '<th>' . $header . '</th>';
    }
    $html .= '</tr></thead>';
    
    // Rows
    $html .= '<tbody>';
    foreach ($rows as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $html .= '<td>' . $cell . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody>';
    
    $html .= '</table>';
    $html .= '</div>';
    
    return $html;
}
}

if (!function_exists('statusBadge')) {
/**
 * Generate a status badge
 *
 * @param string $status Status (success, warning, danger, info)
 * @param string $text Badge text
 * @return string HTML for the badge
 */
function statusBadge($status, $text) {
    return '<span class="badge bg-' . $status . '">' . $text . '</span>';
}
}

if (!function_exists('button')) {
/**
 * Generate a button
 *
 * @param string $text Button text
 * @param string $url Button URL
 * @param string $color Button color (primary, success, warning, danger)
 * @param string $icon Button icon (font awesome)
 * @param string $size Button size (sm, lg)
 * @param bool $outline Whether to use an outline button
 * @return string HTML for the button
 */
function button($text, $url, $color = 'primary', $icon = '', $size = '', $outline = false) {
    $buttonClass = 'btn btn-';
    $buttonClass .= $outline ? 'outline-' : '';
    $buttonClass .= $color;
    
    if (!empty($size)) {
        $buttonClass .= ' btn-' . $size;
    }
    
    $html = '<a href="' . $url . '" class="' . $buttonClass . '">';
    
    if (!empty($icon)) {
        $html .= '<i class="fas fa-' . $icon . ' me-1"></i> ';
    }
    
    $html .= $text . '</a>';
    
    return $html;
}
} 