<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/4/16
 * Time: 13:14
 */

namespace App\Squeegy\Transformers;

use App\UserNote;
use League\Fractal\TransformerAbstract;

class UserNoteTransformer extends TransformerAbstract
{

    public function transform(UserNote $userNote)
    {
        return [
            'id'=>$userNote->id,
            'note'=>$userNote->note,
            'created'=>$userNote->created_at
        ];
    }
}