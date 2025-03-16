<!-- AI Content Generation -->
<div class="row">
    <div class="col-lg-12">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-robot me-1"></i>
                AI Content Generator
            </div>
            <div class="card-body">
                <form method="post" action="<?php echo \Config\App::get('base_url'); ?>/ai-generation/generate">
                    <div class="mb-3">
                        <label for="generationType" class="form-label">Generation Type</label>
                        <select class="form-select" id="generationType" name="type">
                            <option value="summary" <?php echo $generationType === 'summary' ? 'selected' : ''; ?>>Summarize Text</option>
                            <option value="expand" <?php echo $generationType === 'expand' ? 'selected' : ''; ?>>Expand Ideas</option>
                            <option value="analyze" <?php echo $generationType === 'analyze' ? 'selected' : ''; ?>>Detailed Analysis (Research Model)</option>
                            <option value="keyword" <?php echo $generationType === 'keyword' ? 'selected' : ''; ?>>Extract Keywords</option>
                        </select>
                        <div class="form-text">Select the type of content you want to generate.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="prompt" class="form-label">Prompt / Input Text</label>
                        <textarea class="form-control" id="prompt" name="prompt" rows="5" required><?php echo htmlspecialchars($prompt); ?></textarea>
                        <div class="form-text">Enter the text you want to process or a detailed prompt.</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-magic me-2"></i>Generate Content
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($generatedContent)): ?>
<!-- Generated Content Card -->
<div class="row">
    <div class="col-lg-12">
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <i class="fas fa-file-alt me-1"></i>
                Generated Content
                <span class="badge bg-light text-dark ms-2">
                    <?php 
                    $typeLabels = [
                        'summary' => 'Summary',
                        'expand' => 'Expanded Ideas',
                        'analyze' => 'Detailed Analysis',
                        'keyword' => 'Keywords'
                    ];
                    echo $typeLabels[$generationType] ?? 'Generated Text';
                    ?>
                </span>
            </div>
            <div class="card-body">
                <div class="generated-content">
                    <?php echo nl2br(htmlspecialchars($generatedContent)); ?>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <button class="btn btn-sm btn-outline-primary" id="copyBtn" onclick="copyContent()">
                    <i class="fas fa-copy me-1"></i>Copy to Clipboard
                </button>
                
                <a href="<?php echo \Config\App::get('base_url'); ?>/ai-generation" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-trash me-1"></i>Clear Results
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Copy Script -->
<script>
function copyContent() {
    const content = document.querySelector('.generated-content').innerText;
    navigator.clipboard.writeText(content).then(() => {
        const copyBtn = document.getElementById('copyBtn');
        const originalText = copyBtn.innerHTML;
        copyBtn.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
        copyBtn.classList.remove('btn-outline-primary');
        copyBtn.classList.add('btn-success');
        setTimeout(() => {
            copyBtn.innerHTML = originalText;
            copyBtn.classList.remove('btn-success');
            copyBtn.classList.add('btn-outline-primary');
        }, 2000);
    });
}
</script>

<!-- Styling for generated content -->
<style>
.generated-content {
    white-space: pre-line;
    line-height: 1.6;
    max-height: 500px;
    overflow-y: auto;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 5px;
}
</style>
<?php endif; ?>

<!-- How It Works Explanation -->
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <i class="fas fa-info-circle me-1"></i>
                How It Works
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Generation Types:</h5>
                        <ul>
                            <li><strong>Summarize Text:</strong> Creates a concise summary of longer text.</li>
                            <li><strong>Expand Ideas:</strong> Elaborates on a brief concept with more details.</li>
                            <li><strong>Detailed Analysis:</strong> Uses a more powerful research model to provide in-depth insights.</li>
                            <li><strong>Extract Keywords:</strong> Identifies the most important keywords from the provided content.</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5>Tips for Better Results:</h5>
                        <ol>
                            <li>Be clear and specific in your prompts.</li>
                            <li>For summaries, provide enough context in the original text.</li>
                            <li>For idea expansion, start with a well-defined concept.</li>
                            <li>For detailed analysis, include specific aspects you're interested in.</li>
                            <li>Content generation relies on the quality of your input!</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 