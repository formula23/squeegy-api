<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/22/15
 * Time: 11:36
 */

namespace App\Squeegy\Transformers;

use Bican\Roles\Models\Role;
use League\Fractal\TransformerAbstract;

class RoleTransformer extends TransformerAbstract {

    public function transform(Role $role)
    {
        return [
            'id' => (string)$role->id,
            'name' => $role->name,
            'slug' => $role->slug,
            'description' => $role->description,
            'level' => $role->level,
        ];
    }

}