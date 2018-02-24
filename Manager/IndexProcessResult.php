<?php

namespace Mdiyakov\DoctrineSolrBundle\Manager;

class IndexProcessResult
{
    /**
     * @var bool
     */
    private $success = false;

    /**
     * @var string
     */
    private $error = null;

    /**
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @param boolean $success
     */
    public function setSuccess($success)
    {
        $this->success = $success;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }
}