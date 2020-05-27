<?php
namespace MayuraTestToyRobot;

use InvalidArgumentException;

class ToyRobot
{
    //Permissible methods
    const METHOD_PLACE  = 'PLACE';
    const METHOD_MOVE   = 'MOVE';
    const METHOD_LEFT   = 'LEFT';
    const METHOD_RIGHT  = 'RIGHT';
    const METHOD_REPORT = 'REPORT';

    //Permissible directions on tabletop
    const DIRECTION_NORTH = 'NORTH';
    const DIRECTION_EAST  = 'EAST';
    const DIRECTION_SOUTH = 'SOUTH';
    const DIRECTION_WEST  = 'WEST';

    //Permissible rotations on tabletop
    const ROTATION_LEFT  = 'LEFT';
    const ROTATION_RIGHT = 'RIGHT';
    
    protected $tabletop;	//TableTop object
    
    //Position on tabletop.
    protected $x;	//Horizontal
    protected $y;	//Vertical
    protected $direction;	//Direction facing on tabletop
    
    //Directions map
    protected $directionMap = [
        self::DIRECTION_NORTH => self::DIRECTION_EAST,
        self::DIRECTION_EAST  => self::DIRECTION_SOUTH,
        self::DIRECTION_SOUTH => self::DIRECTION_WEST,
        self::DIRECTION_WEST  => self::DIRECTION_NORTH,
    ];

    //Constructor
    public function __construct(TableTop $tabletop)
    {
        $this->tabletop = $tabletop;
    }//constructor

    //Get permissible methods as array or string.
    protected function getMethods($separator = null)
    {
    	$methods = [
    			self::METHOD_PLACE,
    			self::METHOD_MOVE,
    			self::METHOD_LEFT,
    			self::METHOD_RIGHT,
    			self::METHOD_REPORT,
    	];
    	return is_null($separator) ? $methods : implode($separator, $methods);
    }//getMethods
    
    //Get permissible directions as array or string.
    protected function getDirections($separator = null)
    {
    	$directions = [
    			self::DIRECTION_NORTH,
    			self::DIRECTION_EAST,
    			self::DIRECTION_SOUTH,
    			self::DIRECTION_WEST,
    	];
    	return is_null($separator) ? $directions : implode($separator, $directions);
    }//getDirections
    
    
    //Get permissible rotations as array or string.
    protected function getRotations($separator = null)
    {
    	$rotations = [
    			self::ROTATION_LEFT,
    			self::ROTATION_RIGHT,
    	];
    	return is_null($separator) ? $rotations : implode($separator, $rotations);
    }//getRotations
    
    
    //Check whether given direction is a permissible direction.
    protected function isPermissibleDirection($direction)
    {
    	return in_array($direction, $this->getDirections());
    }//isPermissibleDirection
    
    
    //Check whether given rotation is a permissible rotation.
    protected function isPermissibleRotation($rotation)
    {
    	return in_array($rotation, $this->getRotations());
    }//isPermissibleRotation
    
    
    /*** Parse and Execute commands ***/
    //Execute
    public function execute($command)
    {
        //Parse
        extract($this->parseCommand($command));

        //Execute
        switch ($method) 
        {
            case self::METHOD_PLACE:
                $this->place($x, $y, $direction);
                break;
            case self::METHOD_MOVE:
                $this->move();
                break;
            case self::METHOD_LEFT:
            case self::METHOD_RIGHT:
                $this->rotate($method);
                break;
            case self::METHOD_REPORT:
                echo $this->report() . PHP_EOL;
                break;
        }//switch
    }//execute
    
    //Parse command, extracting method, x, y, and direction where applicable.
    protected function parseCommand($command)
    {
    	//echo $this->getDirections('|');
        //Extract method and arguments from command
        preg_match(
            '/^' .
            '(?P<method>' . $this->getMethods('|') . ')' .
            '(\s' .
                '(?P<x>\d+)\s?,' .
                '(?P<y>\d+)\s?,' .
                '(?P<direction>' . $this->getDirections('|') . ')' .
            ')?' .
            '$/',
            strtoupper($command),
            $args
        );
        
        if (!$args)
        {
        	throw new InvalidArgumentException(sprintf('Sorry I could not understand the commands')); 
        }
        
        // Extract captured arguments with fallback defaults
        $method = $args['method'] ?? null;
        $x = $args['x'] ?? 0;
        $y = $args['y'] ?? 0;
        $direction = $args['direction'] ?? self::DIRECTION_NORTH;
        return compact('method', 'x', 'y', 'direction');
    }//parseCommand

    /*** Event Handlers ***/
    // Place robot
    public function place($x, $y, $direction)
    {
    	//echo $direction;
        // Check if coordinates within tabletop boundary
        if (! $this->tabletop->withinBoundary($x, $y)) 
        {
            throw new InvalidArgumentException(sprintf('Coordinates (%d,%d) outside tabletop boundaries.', $x, $y));
        }

        // Check if supplied direction is permissible
        if (! $this->isPermissibleDirection($direction)) 
        {
        	echo "HI";
            throw new InvalidArgumentException(sprintf('Direction (%s) is not recognised.', $direction));
        }

        // Set robot position and direction
        $this->x = $x;
        $this->y = $y;
        $this->direction = $direction;
    }//place
    
    //Move robot forward one unit in current direction
    public function move()
    {
        // Check that robot is placed before executing command
        if (! $this->isPlaced()) return;

        // Get current robot position
        $x = $this->x;
        $y = $this->y;

        // Determine new position based on current direction
        switch ($this->direction) {
            case self::DIRECTION_NORTH:
                $y += 1;
                break;

            case self::DIRECTION_EAST:
                $x += 1;
                break;

            case self::DIRECTION_SOUTH:
                $y -= 1;
                break;

            case self::DIRECTION_WEST:
                $x -= 1;
                break;
        }

        // Check if coordinates within tabletop boundary
        if (! $this->tabletop->withinBoundary($x, $y)) return;

        // Set robot position
        $this->x = $x;
        $this->y = $y;
    }//move

    
    //Rotate robot in by rotation.
    public function rotate($rotation)
    {
        // Check that robot is placed before executing command
        if (! $this->isPlaced()) return;
        $this->direction = $this->resolveDirectionFromRotation($rotation);
    }//rotate

    
    //Report robot status - X,Y position and direction facing.
    public function report()
    {
        // Check that robot is placed before executing command
        if (! $this->isPlaced()) return;
        return sprintf('%d,%d,%s', $this->x, $this->y, $this->direction);
    }//report

    
    // * Check whether robot has been placed on tabletop.
    public function isPlaced()
    {
        return (! is_null($this->x) && ! is_null($this->y));
    }//isPlaced

    
    //Resolve robot direction from given rotation.
    protected function resolveDirectionFromRotation($rotation)
    {
        if (! $this->isPermissibleRotation($rotation)) 
        {
            throw new InvalidArgumentException(sprintf('Rotation (%s) is not recognised.', $rotation));
        }

        // Determine direction of rotation - clockwise or anti-clockwise
        $clockwise = ($rotation === self::ROTATION_RIGHT);
        $mappings = $clockwise ? $this->directionMap : array_flip($this->directionMap);

        return $mappings[$this->direction];
    }//resolveDirectionFromRotation
}//class ToyRobot
