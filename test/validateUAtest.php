<?php
// validateUAtest.php
// Lightweight smoke test to support CI/CD validation. The script ensures that
// critical user acceptance flows (login role selection and credential fields)
// remain present in the rendered login template. It intentionally avoids
// framework dependencies so it can run in minimal CI environments.

class ValidateLoginTemplate
{
    private string $templatePath;

    public function __construct(string $templatePath)
    {
        $this->templatePath = $templatePath;
    }

    public function run(): void
    {
        if (!file_exists($this->templatePath)) {
            throw new RuntimeException("Template not found: {$this->templatePath}");
        }

        $html = file_get_contents($this->templatePath);
        if ($html === false) {
            throw new RuntimeException('Unable to read login template.');
        }

        $requiredSnippets = [
            'name="role"',
            'name="username"',
            'name="password"'
        ];

        foreach ($requiredSnippets as $snippet) {
            if (strpos($html, $snippet) === false) {
                throw new RuntimeException("Missing expected markup: {$snippet}");
            }
        }

        echo "Login template validation passed." . PHP_EOL;
    }
}

try {
    $projectRoot = dirname(__DIR__);
    $validator = new ValidateLoginTemplate($projectRoot . '/views/login.ejs');
    $validator->run();
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
