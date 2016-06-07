<?php

namespace App\Http\Controllers;

use App\Squeegy\Transformers\UserNoteTransformer;
use App\User;
use App\UserNote;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Requests\CreateUserNoteRequest;
use App\Http\Controllers\Controller;


class UserNoteController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct();

        $this->middleware('jwt.auth');
        $this->middleware('user_has_access');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, User $user)
    {
        $notes = $user->notes()->orderBy('created_at', 'desc')->get();
        return $this->response->withCollection($notes, new UserNoteTransformer());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateUserNoteRequest $request)
    {
        $note = $request->users->notes()->create($request->all());
        return $this->response->withItem($note, new UserNoteTransformer());
    }

    /**
     * Display the specified resource.
     *
     * @param $user
     * @param $note
     * @return \Illuminate\Http\Response
     */
    public function show(User $user, UserNote $note)
    {
        return $this->response->withItem($note, new UserNoteTransformer());
    }
}
