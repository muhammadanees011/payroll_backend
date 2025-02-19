<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Interfaces\RealTimeInformationInterface;

class ScheduleEpssubmission extends Command
{
    private $RealTimeInformationRepository;

    public function __construct(RealTimeInformationInterface $RealTimeInformationRepository) {
        parent::__construct();
        $this->RealTimeInformationRepository = $RealTimeInformationRepository;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:epssubmission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $result=$this->RealTimeInformationRepository->handleEPSSubmission();
        dump($result);
    }
}
