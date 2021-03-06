<?php

namespace App\Commands;

use Phinx\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RouteClear extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('route:clear')
             ->setDescription('Flush the routes cache');
    }

    /**
     * Route cleared
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if (file_exists(STORAGE . '/temp/routes.dat')) {
            unlink (STORAGE . '/temp/routes.dat');
        }

        $output->writeln('<info>Routes cleared successfully.</info>');
    }
}
