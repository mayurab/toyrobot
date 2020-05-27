<?php
namespace MayuraTestToyRobot;

class TableTop
{
    protected $height;    //TableTop height
    protected $width;     //TableTop width

    //TableTop Constructor - creates new instance
    public function __construct($height, $width)
    {
        $this->height = $height;
        $this->width = $width;
    }

	//Check given co-ordinates are within boundary or not
	public function withinBoundary($x, $y)
    {
        return (0 <= $x && $x < $this->width) && (0 <= $y && $y < $this->height);
    }//withinBoundary
}//class TableTop
