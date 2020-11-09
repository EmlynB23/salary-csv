<?php
// src/Command/CreateCSVCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DateTime;
use DateInterval;
use DatePeriod;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateSalaryCommand extends Command
{
    // Command to run: $ php bin/console app:salary-csv
    protected static $defaultName = 'app:salary-csv';

    // Config for command. Help and Description set
    protected function configure()
    {
        $this
            ->setDescription('Generates a 12 month salary report.')
            ->setHelp('Running this command generates a 12 month salary report in the form of a csv file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Specify the file to create/open and write data to
        $file = fopen('payment-dates.csv', 'w');

        // Write column headers
        fputcsv($file, array('Period', 'Basic Payment', 'Bonus Payment'));

        // Array to house each generated row to be pushed into csv
        $data = array();

        // Set start date to be now and end to be 12 months after.
        $dateFunc = new DateTime();

        $begin = new DateTime('NOW');
        $end = new DateTime(date('Y-m-d', strtotime('+12 months')));
        $end = $dateFunc->modify('+12 month');

        // Increment by one month with each iteration
        $interval = new DateInterval('P1M');
        $daterange = new DatePeriod($begin, $interval, $end);

        // Statement to loop
        foreach($daterange as $date) {
            // Define month and year in current iteration
            $currentMonth = $date->format("M-y");
            $year = $date->format("Y");
            // Calculate last payday of the month
            $payday = date('Y-m-d', strtotime('last weekday '.date("F Y", strtotime('next month '.$currentMonth. ' '. $year))));
            // Auto assign bonus to fall on 10th
            $bonus = $date->format("Y-m-10");

            //Statement to check if the 10th falls on a weekend. If so, then skip to the next Monday
            if (date("l", strtotime($bonus)) == "Saturday" || date("l", strtotime($bonus)) == "Sunday") {
                $bonusPayday = date('Y-m-d', strtotime('next monday', strtotime($bonus)));
            } else {
                $bonusPayday = $bonus;
            }

            // Create a row of data and push to the csv array
            $newEntry = array($currentMonth, $payday, $bonusPayday);
            array_push($data, $newEntry);
        }

        // Push all entires to csv and close file
        foreach ($data as $row) {
            fputcsv($file, $row);
        }

        fclose($file);

        // Style the output in the command to let the user know it was successful
        $feedbackStyling = new SymfonyStyle($input, $output);

        $feedbackStyling->success('payment-dates.csv has been updated');
        return Command::SUCCESS;

    }
};