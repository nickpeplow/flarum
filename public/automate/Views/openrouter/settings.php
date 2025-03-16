<!-- OpenRouter Settings Card -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-cogs me-1"></i>
                    OpenRouter API Configuration
                </div>
            </div>
            <div class="card-body">
                <form method="post" action="<?php echo \Config\App::get('base_url'); ?>/openrouter/save">
                    <!-- API Key -->
                    <div class="mb-3">
                        <label for="api_key" class="form-label">API Key</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="api_key" name="api_key" value="<?php echo htmlspecialchars($settings['api_key']); ?>" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleApiKey">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Your OpenRouter API key. Get one at <a href="https://openrouter.ai" target="_blank">openrouter.ai</a></div>
                    </div>

                    <div class="row">
                        <!-- Content Model Selection -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="content_model" class="form-label">Content Generation Model</label>
                                <select class="form-select" id="content_model" name="content_model">
                                    <?php foreach ($availableModels as $modelId => $modelName): ?>
                                        <option value="<?php echo htmlspecialchars($modelId); ?>" <?php echo $settings['content_model'] === $modelId ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($modelName); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Used for summaries, expand ideas, and analyze content</div>
                            </div>
                        </div>

                        <!-- Research Model Selection -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="research_model" class="form-label">Research Model</label>
                                <select class="form-select" id="research_model" name="research_model">
                                    <?php foreach ($availableModels as $modelId => $modelName): ?>
                                        <option value="<?php echo htmlspecialchars($modelId); ?>" <?php echo $settings['research_model'] === $modelId ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($modelName); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Used for detailed analysis and more complex tasks</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Max Tokens -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_tokens" class="form-label">Max Tokens</label>
                                <input type="number" class="form-control" id="max_tokens" name="max_tokens" value="<?php echo (int)$settings['max_tokens']; ?>" min="100" max="4096">
                                <div class="form-text">Maximum number of tokens to generate (100-4096)</div>
                            </div>
                        </div>

                        <!-- Temperature -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="temperature" class="form-label">Temperature</label>
                                <input type="number" step="0.1" class="form-control" id="temperature" name="temperature" value="<?php echo (float)$settings['temperature']; ?>" min="0" max="2">
                                <div class="form-text">Controls randomness (0-2). Lower is more deterministic.</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Settings
                        </button>
                        
                        <a href="<?php echo \Config\App::get('base_url'); ?>/openrouter/test-content-model" class="btn btn-outline-info">
                            <i class="fas fa-vial me-1"></i> Test Content Model
                        </a>
                        
                        <a href="<?php echo \Config\App::get('base_url'); ?>/openrouter/test-research-model" class="btn btn-outline-secondary">
                            <i class="fas fa-flask me-1"></i> Test Research Model
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- OpenRouter Usage Statistics -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="fas fa-chart-line me-1"></i>
                OpenRouter API Usage Statistics
            </div>
            <div class="card-body">
                <form method="GET" action="<?= \Config\App::get('base_url') ?>/openrouter/settings" class="mb-4">
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
    </div>
</div>

<!-- OpenRouter API Request Logs -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <i class="fas fa-history me-1"></i>
                OpenRouter API Request Logs
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
                                                data-bs-toggle="modal" data-bs-target="#promptModal" 
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

<!-- Toggle API Key Visibility Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleApiKey');
    const apiKeyInput = document.getElementById('api_key');
    
    toggleBtn.addEventListener('click', function() {
        const type = apiKeyInput.getAttribute('type') === 'password' ? 'text' : 'password';
        apiKeyInput.setAttribute('type', type);
        
        // Toggle icon
        const icon = toggleBtn.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });
    
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