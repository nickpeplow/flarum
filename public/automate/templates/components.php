<?php
/**
 * Reusable UI Components
 * 
 * This file contains functions to generate commonly used UI components
 */

/**
 * Generate a dashboard card with an icon
 * 
 * @param string $title The card title
 * @param string $value The card value
 * @param string $icon Font Awesome icon class
 * @param string $color Card color (primary, success, warning, danger, info)
 * @param string $link Optional link URL
 * @param string $linkText Optional link text
 * @return string HTML for the card
 */
function dashboardCard($title, $value, $icon, $color = 'primary', $link = '', $linkText = 'View Details') {
    $linkHtml = '';
    if (!empty($link)) {
        $linkHtml = '<div class="card-footer d-flex align-items-center justify-content-between">
            <a class="small text-' . $color . ' stretched-link" href="' . $link . '">' . $linkText . '</a>
            <div class="small text-' . $color . '"><i class="fas fa-angle-right"></i></div>
        </div>';
    }
    
    return '<div class="card bg-light mb-4 border-left-' . $color . ' dashboard-card">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col-auto">
                    <div class="h1 mb-0 mr-3 text-' . $color . '"><i class="fas fa-' . $icon . '"></i></div>
                </div>
                <div class="col ml-3">
                    <div class="text-xs font-weight-bold text-' . $color . ' text-uppercase mb-1">' . $title . '</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">' . $value . '</div>
                </div>
            </div>
        </div>
        ' . $linkHtml . '
    </div>';
}

/**
 * Generate a data table
 * 
 * @param array $headers Table header columns
 * @param array $rows Table data rows
 * @param string $tableClass Additional CSS classes for the table
 * @param bool $responsive Whether to wrap the table in a responsive container
 * @return string HTML for the data table
 */
function dataTable($headers, $rows, $tableClass = '', $responsive = true) {
    $html = '';
    
    if ($responsive) {
        $html .= '<div class="table-responsive">';
    }
    
    $html .= '<table class="table table-bordered ' . $tableClass . '" width="100%" cellspacing="0">
        <thead>
            <tr>';
    
    foreach ($headers as $header) {
        $html .= '<th>' . $header . '</th>';
    }
    
    $html .= '</tr>
        </thead>
        <tbody>';
    
    foreach ($rows as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $html .= '<td>' . $cell . '</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</tbody>
    </table>';
    
    if ($responsive) {
        $html .= '</div>';
    }
    
    return $html;
}

/**
 * Generate a card with title and content
 * 
 * @param string $title Card title
 * @param string $content Card content HTML
 * @param string $headerClass Additional CSS classes for the card header
 * @param string $footerContent Optional footer content
 * @return string HTML for the card
 */
function contentCard($title, $content, $headerClass = 'bg-primary text-white', $footerContent = '') {
    $footerHtml = '';
    if (!empty($footerContent)) {
        $footerHtml = '<div class="card-footer">' . $footerContent . '</div>';
    }
    
    return '<div class="card mb-4">
        <div class="card-header ' . $headerClass . '">
            ' . $title . '
        </div>
        <div class="card-body">
            ' . $content . '
        </div>
        ' . $footerHtml . '
    </div>';
}

/**
 * Generate a form group for Bootstrap forms
 * 
 * @param string $labelText Label text
 * @param string $inputHtml HTML for the input element
 * @param string $id Input element ID
 * @param string $helpText Optional help text
 * @return string HTML for the form group
 */
function formGroup($labelText, $inputHtml, $id, $helpText = '') {
    $helpHtml = '';
    if (!empty($helpText)) {
        $helpHtml = '<div id="' . $id . 'Help" class="form-text">' . $helpText . '</div>';
    }
    
    return '<div class="mb-3">
        <label for="' . $id . '" class="form-label">' . $labelText . '</label>
        ' . $inputHtml . '
        ' . $helpHtml . '
    </div>';
}

/**
 * Generate pagination links
 * 
 * @param int $currentPage Current page number
 * @param int $totalPages Total number of pages
 * @param string $baseUrl Base URL for pagination links, will have ?page= appended
 * @param array $params Additional URL parameters as key-value pairs
 * @return string HTML for the pagination links
 */
function pagination($currentPage, $totalPages, $baseUrl, $params = []) {
    if ($totalPages <= 1) {
        return '';
    }
    
    // Build the query string for additional parameters
    $queryString = '';
    foreach ($params as $key => $value) {
        $queryString .= '&' . urlencode($key) . '=' . urlencode($value);
    }
    
    $html = '<nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">';
    
    // Previous button
    $prevDisabled = ($currentPage <= 1) ? ' disabled' : '';
    $html .= '<li class="page-item' . $prevDisabled . '">
        <a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage - 1) . $queryString . '" aria-label="Previous">
            <span aria-hidden="true">&laquo;</span>
        </a>
    </li>';
    
    // Page numbers
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $currentPage + 2);
    
    // Always show first page
    if ($startPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=1' . $queryString . '">1</a></li>';
        if ($startPage > 2) {
            $html .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
        }
    }
    
    // Show page numbers
    for ($i = $startPage; $i <= $endPage; $i++) {
        $active = ($i == $currentPage) ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $baseUrl . '?page=' . $i . $queryString . '">' . $i . '</a></li>';
    }
    
    // Always show last page
    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $html .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $totalPages . $queryString . '">' . $totalPages . '</a></li>';
    }
    
    // Next button
    $nextDisabled = ($currentPage >= $totalPages) ? ' disabled' : '';
    $html .= '<li class="page-item' . $nextDisabled . '">
        <a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage + 1) . $queryString . '" aria-label="Next">
            <span aria-hidden="true">&raquo;</span>
        </a>
    </li>';
    
    $html .= '</ul>
    </nav>';
    
    return $html;
}

/**
 * Generate a status badge
 * 
 * @param string $text Badge text
 * @param string $type Badge type (primary, secondary, success, danger, warning, info)
 * @return string HTML for the badge
 */
function statusBadge($text, $type = 'primary') {
    return '<span class="badge bg-' . $type . '">' . $text . '</span>';
}

/**
 * Generate a modal dialog
 * 
 * @param string $id Modal ID
 * @param string $title Modal title
 * @param string $body Modal body content
 * @param string $footer Modal footer content
 * @param string $size Modal size (sm, lg, xl)
 * @return string HTML for the modal
 */
function modal($id, $title, $body, $footer, $size = '') {
    $sizeClass = !empty($size) ? 'modal-' . $size : '';
    
    return '<div class="modal fade" id="' . $id . '" tabindex="-1" aria-labelledby="' . $id . 'Label" aria-hidden="true">
        <div class="modal-dialog ' . $sizeClass . '">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="' . $id . 'Label">' . $title . '</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ' . $body . '
                </div>
                <div class="modal-footer">
                    ' . $footer . '
                </div>
            </div>
        </div>
    </div>';
} 