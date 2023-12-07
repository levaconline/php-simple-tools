<?php
/**
 * Remove specifficaly marked backups/files (Specificaly by filename or by dir included subdirs).
 * 
 * PHP 8.2.12
 * 
 * @author  Aleksandar Todorovic <levaconline@gmail.com> <aleksandar.todorovic.xyz@gmail.com> <aleksandar.todorovic.777@yandex.com>
 */

class RemoveBackups 
{
    private string $path = '';
    public array $msg = [];
    private string $backupMarker = '_BACKUP';
    private bool $isDir = false; // If dir passed, replace tabs in all files in dir adn subdir files.
    public int $count = 0; // Delered files count.

    public function __construct($path = '', $backupMarker = '_BACKUP_')
    {
        $this->path = $path;
        $this->backupMarker = $backupMarker;
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
            $this->removeBackup($this->path);
        }
    }

    private function findFilesRecursive($path)
    {
        foreach (glob($path. '/*') as $file) {
            if (is_dir($file)) {
                $this->findFilesRecursive($file);
            } else {
                $this->removeBackup($file);
            }
        }
        return;
    }
    
    private function removeBackup($path): bool
    {
        if (strpos($path, $this->backupMarker) === false) {
            return false;
        }

        if (!unlink($path)) {
            $this->msg['Warning'][] = "Can't delete file: " .  $path . ". Please try to delete manyally.\n";
            return false;
        }
        $this->count++;
        $this->msg['Deleted'] = $this->count . " files.\n";

        echo $this->count . ". Removed: " . $path . "\n";


        return true;
    }
}

// CALL //
if (!isset($argv[1]) && !isset($argv[2])) {
    echo "\nERROR: No path passed.\n\n";
    echo "Path to file ot dir is required,\n";
    echo "Try something like following:\n";
    echo "php RemoveBackups.php <some_file.php || some_dir> <backup_marker> \n\n";
    echo "php RemoveBackups.php ../ _BACKUP_ \n\n";
    die();
}

$replacer = new RemoveBackups($argv[1], $argv[2]);
$replacer->run();

var_dump($replacer->msg);

