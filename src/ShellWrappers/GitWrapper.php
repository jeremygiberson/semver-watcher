<?php


namespace SemverWatcher\ShellWrappers;


use SebastianBergmann\Git\Git;

class GitWrapper extends Git
{
    public function cloneRepo($repoUrl, $toDirectory = null)
    {
        $output = $this->execute(
            sprintf('clone %s %s 2>&1', $repoUrl, $toDirectory)
        );
        return $output[0];
    }
}