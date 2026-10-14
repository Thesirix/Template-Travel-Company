<?php

namespace App\View\Components;

use App\Models\FlightGroup;
use Illuminate\View\Component;

class SideBar extends Component
{
    /**
     *
     * @var
     */
    public $flightGroups;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->flightGroups= FlightGroup::with('country')->get();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.agent.common.side-bar');
    }
}
