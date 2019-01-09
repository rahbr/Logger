<?php

NameSpace Elapsed;

class ElapsedTag
{
    const START   = 'start';
    const FINISH  = 'finish';
    const ELAPSED = 'elapsed';
    const PARTIAL = 'partial';

}

class ElapsedTask
{
    protected $_tasks = [];

    private function doStart($taskName)
    {
        $this->_tasks[$taskName][ElapsedTag::START] = microtime(true);
    }

    public function start($taskName, $forced = true)
    {
        if ($forced || empty($this->_tasks[$taskName][ElapsedTag::START])) {
            $this->doStart($taskName);
        }
    }

    private function doStop($taskName)
    {
        //remove partial
        unset($this->_tasks[$taskName][ElapsedTag::PARTIAL]);

        $this->_tasks[$taskName][ElapsedTag::FINISH]  = microtime(true);
        $this->_tasks[$taskName][ElapsedTag::ELAPSED] = $this->_tasks[$taskName][ElapsedTag::FINISH]
            - $this->_tasks[$taskName][ElapsedTag::START];
    }

    public function stop($taskName)
    {
        // stop once
        if (!empty($this->_tasks[$taskName][ElapsedTag::FINISH])) {
            return;
        }
        $this->doStop($taskName);
    }

    private function getEndTask($taskName)
    {
        if (empty($this->_tasks[$taskName][ElapsedTag::FINISH])) {
            return microtime(true);
        }

        return $this->_tasks[$taskName][ElapsedTag::FINISH];
    }

    public function getPartial($taskName)
    {
        $startTask = $this->_tasks[$taskName][ElapsedTag::START];
        // if have partial, use
        if (!empty($this->_tasks[$taskName][ElapsedTag::PARTIAL])) {
            $startTask = $this->_tasks[$taskName][ElapsedTag::PARTIAL];
        }
        // refresh partial
        $endTask                                      = microtime(true);
        $this->_tasks[$taskName][ElapsedTag::PARTIAL] = $endTask;

        return $endTask - $startTask;
    }

    // get Total elapsed time
    public function getElapsed($taskName)
    {
        $endTask   = $this->getEndTask($taskName);
        $startTask = $this->_tasks[$taskName][ElapsedTag::START];
        return $endTask - $startTask;
    }

    public function getArrayElapsed($taskName = '')
    {
        if (!empty($taskName)) {
            return $this->_tasks[$taskName];
        }

        return $this->_tasks;
    }
}