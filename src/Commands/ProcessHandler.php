<?php


namespace SemverWatcher\Commands;


use Monolog\Logger;
use SemverWatcher\ShellWrappers\CheckerWrapper;
use SemverWatcher\ShellWrappers\GitWrapper;

class ProcessHandler
{
    /**
     * @var Logger
     */
    private $log;

    /**
     * ProcessHandler constructor.
     * @param Logger $log
     */
    public function __construct(Logger $log)
    {
        $this->log = $log;
    }


    public function __invoke(ProcessCommand $command)
    {
        list($creds, $projectName) = explode(':', $command->getGitRepoUrl());
        $projectName = preg_replace('/[^A-Za-z0-9\.\-]+/', '_', $projectName);
        $projectDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $projectName;

        $this->log->info(sprintf("processing %s, comparing %s v. %s in %s",
            $command->getGitRepoUrl(), $command->getSourceBranch(), $command->getCompareBranch(), $projectDir));

        if(!file_exists($projectDir)) {
            @mkdir($projectDir);
        }

        $branches = [
            $command->getSourceBranch() => $beforeDir = $projectDir . DIRECTORY_SEPARATOR . $command->getSourceBranch(),
            $command->getCompareBranch() => $afterDir = $projectDir . DIRECTORY_SEPARATOR . $command->getCompareBranch()
        ];

        foreach($branches as $branch => $branchDir) {
            if(!file_exists($branchDir)) {
                @mkdir($branchDir);
                $git = new GitWrapper($branchDir);
                $git->cloneRepo($command->getGitRepoUrl(), $branchDir);
            } else {
                $git = new GitWrapper($branchDir);
            }

            $git->checkout($branch);
        }

        // do compare
        $checker = new CheckerWrapper($this->log);
        $out = $checker->compare($beforeDir, $afterDir);
        return $out;
    }
}