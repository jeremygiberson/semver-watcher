<?php


namespace SemverWatcher\ShellWrappers;


use Monolog\Logger;
use RuntimeException;

class CheckerWrapper
{
    /** @var  Logger */
    private $log;

    /**
     * CheckerWrapper constructor.
     * @param Logger $log
     */
    public function __construct(Logger $log)
    {
        $this->log = $log;
    }


    public function compare($beforeDir, $afterDir)
    {
        $command = sprintf("compare %s %s", escapeshellarg($beforeDir), escapeshellarg($afterDir));
        return $this->execute($command);
    }

    /**
     * @param  string           $command
     * @throws RuntimeException
     */
    protected function execute($command)
    {
        $command = sprintf('php %s/../../vendor/bin/php-semver-checker.phar %s', __DIR__, $command);

        $this->log->info(sprintf("executing %s", $command));
        exec($command, $output, $returnValue);

        if ($returnValue !== 0) {
            throw new RuntimeException(implode("\r\n", $output));
        }

        return $output;
    }
}