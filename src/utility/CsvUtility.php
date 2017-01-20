<?php

namespace Dynamic\CsvUtility\Utility;

use Dynamic\CsvUtility\CsvUtilTraits\CsvUtilityTrait;

/**
 * Class CsvUtility
 * @package Dynamic\CsvUtility\Utility
 */
abstract class CsvUtility
{

    use CsvUtilityTrait;

    /**
     * @var
     */
    private $raw_data;
    /**
     * @var
     */
    private $handle;
    /**
     * @var array
     */
    protected $header_fields = [];
    /**
     * @var string
     */
    private $deliminator = ',';
    /**
     * @var string
     */
    private $enclosure = '"';

    /**
     * CsvUtility constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->setRawData($data);
    }

    /**
     * @param $data
     * @return $this
     */
    public function setRawData($data)
    {
        $this->raw_data = $data;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRawData()
    {
        return $this->raw_data;
    }


    /**
     *
     */
    public function setHandle()
    {
        $this->handle = fopen('php://temp', 'r+');
    }

    /**
     * @return mixed
     */
    protected function getHandle()
    {
        if (!$this->handle) {
            $this->setHandle();
        }
        return $this->handle;
    }

    /**
     * @param $deliminator
     * @return $this
     */
    public function setDeliminator($deliminator)
    {
        $this->deliminator = $deliminator;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeliminator()
    {
        return $this->deliminator;
    }

    /**
     * @param $enclosure
     * @return $this
     */
    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     * @param array $headerFields
     * @return $this
     */
    public function setHeaderFields($headerFields)
    {
        if ((array)$headerFields !== $headerFields) {

        }
        $this->header_fields = $headerFields;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaderFields()
    {
        return $this->header_fields;
    }

    /**
     * @return string
     */
    public function getFileContents()
    {
        return $this->generateFileData();
    }

    /**
     * @return \Generator
     */
    protected function iterateData()
    {
        foreach ($this->getRawData() as $dataItem) {
            yield $dataItem;
        }
    }

    /**
     * @return string
     */
    protected function generateFileData()
    {
        $contents = '';

        if (!empty($this->getHeaderFields())) {
            $this->putCSV($this->getHandle(), $this->getHeaderFields());
        }

        foreach ($this->iterateData() as $data) {
            $this->addFileContents($data);
        }
        $handle = $this->getHandle();
        rewind($handle);

        while (!feof($this->getHandle())) {
            $contents .= fread($handle, 8192);
        }

        fclose($handle);
        return $contents;
    }

    /**
     * @param array $data
     * @return \Generator
     */
    protected function iterateRowData($data = [])
    {
        foreach ($data as $key => $val) {
            yield $val;
        }
    }

    /**
     * @param array $row
     */
    protected function addFileContents($row = [])
    {
        $deliminator = $this->getDeliminator();
        $enclosure = $this->getEnclosure();

        $row = $this->preProcessData($row);

        if ((array)$row === $row) {
            $data = [];
            foreach ($this->iterateRowData($row) as $dataItem) {
                $data[] = $dataItem;
            }
            $this->putCSV($this->getHandle(), $data, $deliminator, $enclosure);
        }
    }

    /**
     * @param $handle
     * @param $data
     * @param string $deliminator
     * @param string $enclosure
     * @return \Generator
     */
    protected function putCSV($handle, $data, $deliminator = ',', $enclosure = '"')
    {
        yield fputcsv($handle, $data, $deliminator, $enclosure);
    }


}