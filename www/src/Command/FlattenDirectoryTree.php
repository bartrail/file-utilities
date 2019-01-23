<?php
/**
 * move-files
 * Created by PhpStorm.
 * File: MoveFiles.php
 * User: con
 * Date: 2019-01-23
 * Time: 08:51
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class FlattenDirectoryTree extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:flatten-directory-tree';

    protected $sourceFiles = [];
    protected $ignore = ['.DS_Store'];

    protected function configure()
    {
        $this->addArgument('source-dir', InputArgument::REQUIRED);
        $this->addArgument('target-dir', InputArgument::REQUIRED);
        $this->addOption(
            'overwrite',
            null,
            InputOption::VALUE_NONE,
            'Overwrite existing files (asks for every file unless <comment>-n</comment> is given)'
        );
        $this->addOption(
            'ignore',
            'i',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'File patterns to be ignored',
            ['.DS_Store']
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // ...

        $this->ignore = $input->getOption('ignore');
        $sourceDir    = realpath($input->getArgument('source-dir'));

        if (!is_dir($sourceDir)) {
            throw new \InvalidArgumentException(sprintf('Source Directory [%s] does not exist', $sourceDir));
        }
        $targetDir = $input->getArgument('target-dir');

        if (!is_dir($targetDir)) {
            try {
                mkdir($targetDir);
            } catch (\Exception $e) {
                throw new \Exception(sprintf("Can't create directory [%s]", $targetDir));
            }
        }

        $output->writeln(sprintf('Scanning directory [%s]', $sourceDir));

        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($sourceDir));
        foreach ($rii as $file) {

            if ($file->isDir()) {
                continue;
            }

            $this->sourceFiles[] = $file->getPathname();
        }

        asort($this->sourceFiles);

        $output->writeln(sprintf('Copy [%s] files to directory [%s] ', count($this->sourceFiles), $sourceDir));

        $progress = new ProgressBar($output, count($this->sourceFiles));
        $progress->setFormat('debug');

        $overwrite = $input->getOption('overwrite') ? ' Y/n ' : 'y/N';

        foreach ($this->sourceFiles as $i => $absolutePath) {

            foreach ($this->ignore as $ignore) {
                if (strstr($absolutePath, $ignore)) {
                    continue 2;
                }
            }

            $relativePath = substr($absolutePath, strlen($sourceDir) + 1);
            $newFileName  = str_replace('/', '_', $relativePath);
            $newPathName  = sprintf('%s/%s', $targetDir, $newFileName);

            try {
                $copyFile = true;
                if (file_exists($newPathName)) {
                    $helper   = $this->getHelper('question');
                    $question = new ConfirmationQuestion(
                        sprintf("Overwrite file [<info>%s</info>]? %s\n", $newPathName, $overwrite),
                        $input->getOption('overwrite')
                    );

                    if ($helper->ask($input, $output, $question)) {
                        $copyFile = true;
                    } else {
                        $copyFile = false;
                    }
                }

                if ($copyFile) {
                    if ($output->isVerbose()) {
                        $output->writeln(
                            sprintf(
                                ' copy file [<comment>%s</comment>] to [<info>%s</info>]',
                                $absolutePath,
                                $newPathName
                            )
                        );
                    }
                    copy($absolutePath, $newPathName);
                } else {
                    if ($output->isVerbose()) {
                        $output->writeln(
                            sprintf(' skipping file [<comment>%s</comment>]', $absolutePath)
                        );
                    }
                }
            } catch (\Exception $e) {
                $output->writeln(sprintf(' <error>Failed to copy [%s] to [%s]</error>', $absolutePath, $newPathName));
            }
            $progress->advance();

        }
        $progress->finish();

        $output->writeln('');
        $output->writeln('done.');

    }

}