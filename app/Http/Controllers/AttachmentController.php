<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class AttachmentController extends Controller
{
    /**
     * Display a listing of the attachments.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $attachments = Attachment::all();
        return response()->json($attachments);
    }

    /**
     * Store a newly created attachment in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'attachment_url' => 'required|string',
        ]);

        $attachment = Attachment::create($request->all());
        return response()->json($attachment, 201);
    }

    /**
     * Display the specified attachment.
     *
     * @param \App\Models\Attachment $attachment
     * @return \Illuminate\Http\Response
     */
    public function show(Attachment $attachment)
    {
        return response()->json($attachment);
    }

    /**
     * Update the specified attachment in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Attachment $attachment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Attachment $attachment)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'attachment_url' => 'required|string',
        ]);

        $attachment->update($request->all());
        return response()->json($attachment);
    }

    /**
     * Remove the specified attachment from storage.
     *
     * @param \App\Models\Attachment $attachment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Attachment $attachment)
    {
        $attachment->delete();
        return response()->json(null, 204);
    }
}
