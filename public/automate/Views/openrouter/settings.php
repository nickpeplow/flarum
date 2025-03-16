<!-- OpenRouter Settings Card -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-cogs me-1"></i>
                OpenRouter API Configuration
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
                        
                        <a href="<?php echo \Config\App::get('base_url'); ?>/openrouter/test-connection" class="btn btn-info">
                            <i class="fas fa-plug me-1"></i> Test Connection
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- OpenRouter Information Card -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="fas fa-info-circle me-1"></i>
                About OpenRouter
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>What is OpenRouter?</h5>
                        <p>OpenRouter is a unified API that provides access to various AI models (like Claude, GPT-4, etc.) through a single endpoint. This allows you to use different AI models for different purposes without changing your code.</p>
                        
                        <h5>Available Features</h5>
                        <ul>
                            <li><strong>AI Content Generator:</strong> Generate summaries, expand ideas, and analyze content</li>
                            <li><strong>Keyword Extraction:</strong> Automatically extract keywords from text</li>
                            <li><strong>OpenRouter Test:</strong> Test different models and prompts</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5>Model Recommendations</h5>
                        <ul>
                            <li><strong>For Content Generation:</strong> Claude 3 Haiku or GPT-4o Mini are fast and cost-effective.</li>
                            <li><strong>For Research:</strong> Claude 3.7 Sonnet or Claude 3 Opus provide more detailed analysis.</li>
                            <li><strong>For Free Usage:</strong> DeepSeek R1 (Free) provides good results without cost.</li>
                        </ul>
                        
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Note:</strong> Using different models may incur different costs. Check the <a href="https://openrouter.ai/docs#models" target="_blank">OpenRouter pricing</a> for details.
                        </div>
                    </div>
                </div>
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
});
</script> 