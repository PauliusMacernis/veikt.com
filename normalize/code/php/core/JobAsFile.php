<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-11-03
 * Time: 18:03
 */

namespace NormalizeCore;


class JobAsFile
{
    protected $entranceDir;
    protected $downloadedPostDir;
    protected $filesContentText;
    protected $normalizedContent;
    protected $filesToSkip;


    public function __construct($entranceDir, $downloadedPostDir)
    {
        $this->setFilesToSkip();
        $this->setEntranceDir($entranceDir);
        $this->setDownloadedPostDir($downloadedPostDir);
        $this->setFilesContentText();
    }

    protected function setFilesToSkip()
    {
        $this->filesToSkip = ['.', '..', '.gitignore'];
    }

    protected function setEntranceDir($entranceDir)
    {
        $this->entranceDir = $entranceDir;
    }

    protected function setDownloadedPostDir($downloadedPostDir)
    {
        $this->downloadedPostDir = $downloadedPostDir;
    }

    public function validateDownloaded()
    {

        $filesContentText = $this->getFilesContentText();

        $Settings = new Settings($this->entranceDir);

        if (!$Settings->isContentValid($filesContentText)) {
            $message = "Downloaded content does not match requirements of existing settings: " . $this->downloadedPostDir;
            throw new \LogicException($message);
        }

    }

    public function getFilesContentText()
    {
        return $this->filesContentText;
    }

    protected function setFilesContentText()
    {

        $result = array();
        $filesToSkip = $this->filesToSkip;

        // Get downloaded files of the job ad
        $files = scandir($this->downloadedPostDir);
        $files = array_filter($files, function ($file) use ($filesToSkip) {
            if (in_array($file, $filesToSkip)) {
                return false;
            }
            return true;
        });
        if (empty($files)) {
            $this->filesContentText = $result;
            return $result;
        }

        foreach ($files as $file) {
            $result[$file] = $this->fileGetContent($this->downloadedPostDir . DIRECTORY_SEPARATOR . $file);
        }

        $this->filesContentText = $result;
        return $result;

    }

    public function normalize($normalizerClass, $transformerClass)
    {

        $Normalizer = new $normalizerClass;
        $Transformer = new $transformerClass;

        $transformedData = $Transformer->transform($this->getFilesContentText());

        $Settings = new Settings($this->entranceDir);
        $SettingsAll = $Settings->getAll();

        $this->normalizedContent = array();
        foreach ($SettingsAll['content-to-extract-from-files'] as $name => $details) {

            if (
                !isset($details['required'])
                || (!$details['required'] && !method_exists($Normalizer, $name))
            ) {

                // If $name is not $required and there is no method then skip it
                continue;
            }

            if ($details['required'] && !method_exists($Normalizer, $name)) {
                $message = 'Normalizer "' . $normalizerClass
                    . '" must have a method named "'
                    . $name . '". This method is used to extract "' . $name
                    . '" content from downloaded job posting. '
                    . 'This method is required by settings.json.';
                throw new \LogicException($message);
            }

            $this->normalizedContent[$name] = $Normalizer->$name($transformedData);

        }

    }

    /**
     * @param string $writerToDbClass
     * @return bool
     */
    public function writeNormalizedContentToDb($writerToDbClass)
    {

        if (empty($this->normalizedContent)) {
            return true;
        }

        /**
         * @var \NormalizeCore\JobContentToDbWriter $Writer
         */
        $Writer = new $writerToDbClass($this->entranceDir, $this->normalizedContent);
        $Writer->write();


    }

    public function validateNormalized()
    {

        $Settings = new Settings($this->entranceDir);

        if (!$Settings->isNormalizedContentValid($this->getNormalizedContent())) {
            $message = "Normalized content does not match requirements of existing settings: " . $this->downloadedPostDir;
            throw new \LogicException($message);
        }

    }

    protected function getNormalizedContent()
    {
        return $this->normalizedContent;
    }

    public function validateWritten()
    {
        //@todo
        return true;
    }

    public function removeDownloadedFiles()
    {
        $dirPath = $this->downloadedPostDir;

        if ($this->dirContainsOtherDir($dirPath)) {
            throw new \Exception("Cannot remove directory if it contains any other directory. Tried to remove: " . $dirPath);
        }

        // Remove every file by file name
        $this->removeFilesMatchingPropertiesOfFilesContentText($dirPath);

        // Dir should be empty at the moment so removing empty should not cause any problems.
        return rmdir($dirPath);

    }

    protected function dirContainsOtherDir($dirPath)
    {
        $this->validateDirOrFail($dirPath);
        return $this->isDirContainingOtherDir($dirPath);
    }

    protected function validateDirOrFail($dirPath)
    {
        if (!is_dir($dirPath)) {
            throw new \Exception($dirPath . " is not set");
        }
    }

    protected function isDirContainingOtherDir($dirPath)
    {
        $files = scandir($dirPath);
        foreach ($files as $file) {
            if (in_array($file, $this->filesToSkip)) {
                continue;
            }
            if (is_dir($dirPath . DIRECTORY_SEPARATOR . $file)) {
                return true;
            }
        }

        return false;

    }

    private function removeFilesMatchingPropertiesOfFilesContentText($dirPath)
    {
        foreach ($this->filesContentText as $keyRepresentingFilename => $otherInfo) {
            $file = $dirPath . DIRECTORY_SEPARATOR . $keyRepresentingFilename;
            if (!is_file($file)) {
                throw new \Exception("A file was expected, but the given was not the file given. The file given: " . $file);
            }
            $success = unlink($file);
            if (!$success) {
                throw new \Exception("Action removing the file returned false which means file was not removed successfully or other problems. Check by human is required.");
            }
        }
    }


    protected function fileGetContent($filePath)
    {
        return file_get_contents($filePath);
    }

}