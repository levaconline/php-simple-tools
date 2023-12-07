<?php

class Tab2Spaces 
{
    public int $spaces = 4;
    private string $path = '';
    public array $msg = [];
    private string $backupMarker = '_BACKUP';
    private bool $isDir = false; // If dir passed, replace tabs in all files in dir adn subdir files.
    private array $targerExetnsion = ['php']; // Affect only files with specified extensions. (if array empty, affect all files.)

    public function __construct($path = '')
    {
        $this->path = $path;
        $this->backupMarker = '_BACKUP_' . time();
    }

    public function run(): void
    {
        if (!$this->validate() ){
            return;
        }

        $this->isDir = is_dir($this->path) ? true : false;

        $this->manageProcess();
    }

    private function validate(): bool
    {
        if (!file_exists($this->path)) {
            $this->msg['Errors'][] = "\n\nNot found; "  . $this->path . "\n";

            return false;
        }

        if (!is_readable($this->path) || !is_writeable($this->path)) {
            $this->msg['Errors'][] = "\n\nNo enough permissions on; "  . $this->path . "\n";

            return false;
        }

        return true;
    }

    private function manageProcess(): void
    {
        // Go recursive.
        if ($this->isDir) {
            $this->findFilesRecursive($this->path);
        } else {
            // Single file;
            $this->replaceTabs($this->path);
        }
    }

    private function findFilesRecursive($path)
    {
        foreach (glob($path. '/*') as $file) {
            //echo "\n" . $file . "\n";
            if (is_dir($file)) {
                //echo "\ndir: " . $file . "\n";
                $this->findFilesRecursive($file);
            } else {
                $this->replaceTabs($file);
                //echo "\nfile: " . $file . "\n";
            }
        }
        return;
    }

    private function replaceTabs($path = ''): void
    {
        if (!empty($this->targerExetnsion)) {
            $fi = pathinfo($path);
            if (!in_array( $fi['extension'], $this->targerExetnsion)) {
                return;
            }
        }

        if (!$this->makeBackup($path)) {
            return;
        }
echo $path , "\n";
        $source = file_get_contents($path);
        $managedSource = str_replace("\t", str_repeat(' ', $this->spaces), $source);
        file_put_contents($path, $managedSource);
        echo "managed,\n";
    }

    private function makeBackup($path): bool
    {
        if (!copy($path, $path . $this->backupMarker)) {
            echo "Could not make backup" .  $path . "\n";
            return false;
        }

        return true;
    }
}

// CALL //
if (!isset($argv[1])) {
    echo "\nERROR: No path passed.\n\n";
    echo "Path to file ot dir is required,\n";
    echo "Try something like following:\n";
    echo "php Tab2Spaces.php file.php \n\n";
    die();
}

$replacer = new Tab2Spaces($argv[1]);
$replacer->run();

