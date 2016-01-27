<?php

namespace nbulaienko\sortable;

use Illuminate\Support\Facades\Input;

trait SortableTrait {

    protected $using_join = false;
    protected $join_table;
    protected $join_name;
    protected $join_rule;

    protected $sorting_column;

    public function scopeSortable($query, array $defaultOrder = [])
    {
        if (Input::has('s') && Input::has('o')) {
            $this->processMap();
            $order = Input::get('o');

            if ($this->using_join) {
                return $query->join($this->join_table . ' as ' . $this->join_name, sprintf($this->join_rule['lcol'], $this->join_name), $this->join_rule['sign'], sprintf($this->join_rule['rcol'], $this->table))->orderBy($this->join_name . '.' . $this->sorting_column, $order);
            } else {
                return $query->orderBy(Input::get('s'), $order);
            }

        } else {
        	if (!empty($defaultOrder)) $query->orderBy($defaultOrder[0], isset($defaultOrder[1]) ? $defaultOrder[1] : 'desc');
            return $query;
        }
    }

    protected function processMap()
    {
        $column   = Input::get('s');

        $mapItems = explode('_', $column);
        $itemsCnt = count($mapItems);

        if ($itemsCnt > 1) {
            $joinName = $mapItems[0];

            if ($itemsCnt > 2) {
                unset($mapItems[0]);
                $column = implode('_', $mapItems);
            } else {
                $column = $mapItems[1];
            }

            if (isset($this->sortingMap[$joinName])) {
                $map   = $this->sortingMap[$joinName];
                $this->join_name  = $joinName;
                $this->join_table = $map['table'];
                $this->join_rule  = $map['rule'];
                if (!in_array($column, $map['columns'])) $column = null;
                $this->using_join = true;
                $this->sorting_column = $column;
            }
        }
    }

    public static function link_to_sorting_action($col, $title = null)
    {
        if (is_null($title)) {
            $title = str_replace('_', ' ', $col);
            $title = ucfirst($title);
        }

        $indicator = (Input::get('s') == $col ? (Input::get('o') === 'asc' ? '&uarr;' : '&darr;') : null);
        $parameters = array_merge(Input::get(), array('s' => $col, 'o' => (Input::get('o') === 'asc' ? 'desc' : 'asc')));

        return link_to_route(\Route::getCurrentRoute()->getName(), "$title $indicator", $parameters);
    }
}
