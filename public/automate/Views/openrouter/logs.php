<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">OpenRouter API Usage Statistics</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?= \Config\App::get('base_url') ?>/openrouter/logs" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                    </form>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Total Requests</h5>
                                    <p class="card-text display-4"><?= number_format($stats['overall']['total_requests'] ?? 0) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Total Tokens</h5>
                                    <p class="card-text display-4"><?= number_format($stats['overall']['total_tokens'] ?? 0) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Total Cost</h5>
                                    <p class="card-text display-4">$<?= number_format(($stats['overall']['total_cost'] ?? 0), 4) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($stats['by_model'])): ?>
                    <h5 class="mt-4">Usage by Model</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Model</th>
                                    <th>Requests</th>
                                    <th>Tokens</th>
                                    <th>Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['by_model'] as $model): ?>
                                <tr>
                                    <td><?= htmlspecialchars($model['model']) ?></td>
                                    <td><?= number_format($model['request_count']) ?></td>
                                    <td><?= number_format($model['total_tokens']) ?></td>
                                    <td>$<?= number_format($model['total_cost'], 4) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">OpenRouter API Request Logs</h5>
                    <a href="<?= \Config\App::get('base_url') ?>/openrouter/settings" class="btn btn-sm btn-outline-primary">Settings</a>
                </div>
                <div class="card-body">
                    <?php if (empty($logs)): ?>
                        <div class="alert alert-info">No logs found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Model</th>
                                        <th>Type</th>
                                        <th>Prompt</th>
                                        <th>Tokens</th>
                                        <th>Cost</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?= $log['id'] ?></td>
                                        <td><?= htmlspecialchars($log['created_at']) ?></td>
                                        <td><?= htmlspecialchars($log['model']) ?></td>
                                        <td><?= htmlspecialchars($log['request_type']) ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-link prompt-btn" 
                                                    data-toggle="modal" data-target="#promptModal" 
                                                    data-prompt="<?= htmlspecialchars($log['prompt']) ?>"
                                                    data-response="<?= htmlspecialchars($log['response_text']) ?>">
                                                View
                                            </button>
                                        </td>
                                        <td><?= number_format($log['total_tokens'] ?? 0) ?></td>
                                        <td>$<?= number_format(($log['cost'] ?? 0), 6) ?></td>
                                        <td>
                                            <?php if ($log['status'] === 'completed'): ?>
                                                <span class="badge bg-success">Completed</span>
                                            <?php elseif ($log['status'] === 'failed'): ?>
                                                <span class="badge bg-danger" title="<?= htmlspecialchars($log['error_message']) ?>">Failed</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page-1 ?>&start_date=<?= htmlspecialchars($startDate) ?>&end_date=<?= htmlspecialchars($endDate) ?>">Previous</a>
                                </li>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($page + 2, $totalPages); $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&start_date=<?= htmlspecialchars($startDate) ?>&end_date=<?= htmlspecialchars($endDate) ?>"><?= $i ?></a>
                                </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page+1 ?>&start_date=<?= htmlspecialchars($startDate) ?>&end_date=<?= htmlspecialchars($endDate) ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for viewing prompt and response -->
<div class="modal fade" id="promptModal" tabindex="-1" aria-labelledby="promptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="promptModalLabel">Prompt & Response</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6>Prompt:</h6>
                    <div class="p-3 bg-light rounded" id="modalPrompt"></div>
                </div>
                <div>
                    <h6>Response:</h6>
                    <div class="p-3 bg-light rounded" id="modalResponse"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle prompt modal
    const promptModal = document.getElementById('promptModal');
    if (promptModal) {
        promptModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const prompt = button.getAttribute('data-prompt');
            const response = button.getAttribute('data-response');
            
            document.getElementById('modalPrompt').textContent = prompt;
            document.getElementById('modalResponse').textContent = response;
        });
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?> 