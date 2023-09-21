<?php

namespace App\Command;

use App\Manager\GeolocationManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(name: 'app:calculate-distance')]
class CalculateDistanceCommand extends Command
{
    public function __construct(
        private SerializerInterface $serializer,
        private GeolocationManager $geolocationManager,
        #[Autowire('%kernel.project_dir%')]
        private $rootDir,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $addressesWithDistance = $this->geolocationManager->calculateDistanceToAdchieve();
        usort($addressesWithDistance, function ($item1, $item2) {
            return $item1['distance'] <=> $item2['distance'];
        });

        foreach ($addressesWithDistance as $key => $item) {
            $index = $key+1;
            $output->writeln("Record ".$index);
            $output->writeln("Name: ".$item['name']);
            $output->writeln("Distance: ".$item['distance']);

            $addressesWithDistance[$key] = array_merge($item, ['sortnumber' => $index]);
        }

        //write to csv
        $destinationFile = "{$this->rootDir}/files/distances.csv";
        file_put_contents($destinationFile, $this->serializer->serialize($addressesWithDistance, 'csv',
            ['csv_headers' => ['sortnumber', 'name', 'address', 'distance']]
        ));


        return Command::SUCCESS;
    }
}