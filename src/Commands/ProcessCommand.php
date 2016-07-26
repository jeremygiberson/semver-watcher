<?php


namespace SemverWatcher\Commands;


class ProcessCommand
{
    /** @var  string */
    private $gitRepoUrl;
    /** @var  string */
    private $sourceBranch;
    /** @var  string */
    private $compareBranch;

    /**
     * ProcessCommand constructor.
     * @param string $gitRepoUrl
     * @param string $sourceBranch
     * @param string $compareBranch
     */
    public function __construct($gitRepoUrl, $sourceBranch, $compareBranch)
    {
        $this->gitRepoUrl = $gitRepoUrl;
        $this->sourceBranch = $sourceBranch;
        $this->compareBranch = $compareBranch;
    }

    /**
     * @return string
     */
    public function getGitRepoUrl()
    {
        return $this->gitRepoUrl;
    }

    /**
     * @return string
     */
    public function getSourceBranch()
    {
        return $this->sourceBranch;
    }

    /**
     * @return string
     */
    public function getCompareBranch()
    {
        return $this->compareBranch;
    }


}