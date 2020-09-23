<?php

class VS7_Downloader_Model_Observer
{
    public function processFile($event)
    {
        $response = $event->getFront()->getResponse();
        $headers = $response->getHeaders();
        foreach ($headers as $header)
        {
            if ($header['name'] == 'Content-Disposition') {
                preg_match('/filename=\"([^\"]+)\"/', $header['value'], $matches);
                if (
                    !isset($matches[1])
                    || empty($matches[1])
                ) {
                    return;
                }
                $fileName = $matches[1];
                $path = $this->_createPath($fileName);
                file_put_contents($path, $response->getBody());
                $url = substr($path, strlen(Mage::getBaseDir()));
                $response
                    ->clearHeaders()
                    ->clearBody();
                $response
                    ->setRedirect($url);
            }
        }
    }

    protected function _createPath($fileName)
    {
        $path = Mage::getBaseDir('media') . DS . 'vs7_downloader' . DS . uniqid() . DS . $fileName;
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        if (
            (file_exists($path) && !is_writable($path))
            || (!file_exists($path) && !is_writable(dirname($path)))
        ) {
            Mage::throwException($path . ' is not writable');
        }

        return $path;
    }
}