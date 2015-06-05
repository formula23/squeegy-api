<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/3/15
 * Time: 10:55
 */

namespace App\OctaneLA\Transformers;


abstract class Transformer {

    /**
     *
     * Transform a collection of items
     *
     * @param $items
     * @return array
     */

    public function transformCollection(array $items)
    {
        return array_map([$this, 'transform'], $items);
    }

    public abstract function transform($item);

} 