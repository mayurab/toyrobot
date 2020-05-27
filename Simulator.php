<?php
namespace MayuraTestToyRobot;

class Simulator
{
    protected $robot; //ToyRobot instance 
    
    //Constructor Simulator
    public function __construct(ToyRobot $robot)
    {
        $this->robot = $robot;
    }
    //Run simulator
    public function run($source)
    {
        $handle = fopen($source, 'r');
        while (($command = fgets($handle))) 
        {
            $this->robot->execute($command);
        }//while
        fclose($handle);
    }//run
}//class Simulator
