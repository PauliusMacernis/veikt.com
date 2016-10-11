<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-10-09
 * Time: 22:56
 */

namespace Core;


class Job
{

    public function __construct(
        \Symfony\Component\DomCrawler\Crawler $Content,
        array $filesRequiredToOutput,
        $url,
        array $projectSettings
    ) {

        if(empty($filesRequiredToOutput)) {
            return $this;
        }

        foreach ($filesRequiredToOutput as $fileName => $fileData) {
            //$setMethodName = 'set' . ucfirst(strtolower($fileName));

            if(method_exists($this, $fileName)) {
                $this->$fileName($fileName, $Content, $url, $projectSettings);
            } else {
                $this->{$fileName} = null;
            }

        }

        return $this;

    }

    /**
     * This method is used as default method to set default values
     *  for properties that have no individual set{FileName} method.
     * Simply saying, this is the method we use for:
     *  "The feature of extracting content for $name file does not exist."
     *
     * For example, if the new file requirement is being added globally then such
     *  file will be created immediately by using this method. However,
     *  no content will be passed to the file, because
     *  the content extraction logic is not ready while not added by a developer.
     *
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->$name = isset($value) ? $value : null;
    }

    /**
     * Gets property value.
     *  Property name is the same as file name from
     *  settings.js ["files-required-to-output"]
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            default:
                return $this->$name;
        }
    }




}