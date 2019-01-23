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

class RandomizeDirectory extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:randomize-directory';

    /**
     * @var array|\SplFileInfo
     */
    protected $sourceFiles = [];
    protected $ignore = ['.DS_Store'];

    protected function configure()
    {
        $this->addArgument('source-dir', InputArgument::REQUIRED);
        $this->addOption(
            'prefix-separator',
            null,
            InputOption::VALUE_OPTIONAL,
            'The Prefix separator character',
            '___'
        );
        $this->addOption(
            'replace-prefix',
            null,
            InputOption::VALUE_NONE,
            'If given, an already randomized folder can be re-randomized without adding a new prefix'
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
        $this->ignore = $input->getOption('ignore');
        $separator    = $input->getOption('prefix-separator');
        $sourceDir    = realpath($input->getArgument('source-dir'));

        if (!is_dir($sourceDir)) {
            throw new \InvalidArgumentException(sprintf('Source Directory [%s] does not exist', $sourceDir));
        }

        $output->writeln(sprintf('Scanning directory [%s]', $sourceDir));

        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($sourceDir));
        foreach ($rii as $file) {

            if ($file->isDir()) {
                continue;
            }

            $this->sourceFiles[] = $file;
        }

        shuffle($this->sourceFiles);


        $fileAmount    = count($this->sourceFiles);
        $replacePrefix = $input->getOption('replace-prefix');

        if ($replacePrefix) {
            $pattern = sprintf('/[0-9]{%s}%s/', strlen($fileAmount), $separator);
        }

        $output->writeln(sprintf('Renaming [%s] files in directory [%s] ', $fileAmount, $sourceDir));

        $progress = new ProgressBar($output, $fileAmount);
        $progress->setFormat('debug');

        foreach ($this->sourceFiles as $i => $file) {

            foreach ($this->ignore as $ignore) {
                if (strstr($file->getPathname(), $ignore)) {
                    continue 2;
                }
            }

            $fileName = $file->getFilename();
            if ($replacePrefix) {
                if (preg_match($pattern, $file->getFilename())) {
                    $fileName = preg_replace($pattern, '', $file->getFilename());
                }
            }

            $prefix      = sprintf('%s%s', $i, $separator);
            $newLength   = strlen($fileName) + strlen($fileAmount) + strlen($separator);
            $newFileName = str_pad(sprintf('%s%s', $prefix, $fileName), $newLength, '0', STR_PAD_LEFT);
            $newPathName = sprintf('%s/%s', $file->getPath(), $newFileName);

            try {
                if ($output->isVerbose()) {
                    $output->writeln(
                        sprintf(
                            ' rename file [<comment>%s</comment>] to [<info>%s</info>]',
                            $file->getPathname(),
                            $newPathName
                        )
                    );
                }
                rename($file->getPathname(), $newPathName);
            } catch (\Exception $e) {

                $output->writeln(
                    sprintf(' <error>Failed to rename [%s] to [%s]</error>', $file->getPathname(), $newPathName)
                );

            }
            $progress->advance();

        }
        $progress->finish();

        $output->writeln('');
        $output->writeln('done.');

    }

}