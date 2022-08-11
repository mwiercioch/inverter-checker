<?php

// src/Command/CreateUserCommand.php
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'app:check-inverter')]
class CheckInverterCommand extends Command
{
    protected static $defaultName = 'app:check-inverter';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $turnOff = true;
        $httpClient = HttpClient::create();
        try {
            $response = $httpClient->request('GET',  $_ENV['INVERTER_ENDPOINT'], ['timeout' => 3]);

            $statusCode = $response->getStatusCode();

            if ($statusCode != 200) {
                $output->writeln('<info>Inverter public link is unavailable</info>');
            } else {
                $json = json_decode($response->getContent());

                $data = html_entity_decode($json->data);
                $data = json_decode($data, true);

                if(array_key_exists('realKpi', $data)) {
                    if(array_key_exists('realTimePower', $data['realKpi'])) {
                        $power = floatval($data['realKpi']['realTimePower']);
                        if($power >= floatval($_ENV['INVERTER_POWER_LEVEL_TO_TURN_ON'])) {
                            $turnOff = false;
                        }
                    }
                }
            }
        } catch(\Exception $e) {
            $output->writeln('<info>Inverter public link is unavailable</info>');
        }

        try {
            if($turnOff == true) {
                $httpClient->request('GET', $_ENV['TURN_OFF_ENDPOINT'], ['timeout' => 3]);
                $output->writeln('<info>Power Off</info>');
            } else {
                $httpClient->request('GET', $_ENV['TURN_ON_ENDPOINT'], ['timeout' => 3]);
                $output->writeln('<info>Power On</info>');
            }
        } catch (\Exception $e) {
            $output->writeln('<error>Switching power failure: '.$e->getMessage().'</error>');
        }

        return Command::SUCCESS;
    }
}