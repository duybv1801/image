<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use App\Models\Album;

class AlbumController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $albums = Album::all();
        return response()->json(['albums' => $albums]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:png,jpg,svg|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Image validation failed', 'errors' => $validator->errors()], 400);
        }

        $album = new Album();
        if ($request->hasFile('image')) {
            // $imageHash = substr(md5(uniqid()), 0, 2);
            // $imageName = date('Ymd') . $imageHash . '.' . $request->file('image')->getClientOriginalExtension();
            $imageName = date('Ymd') . '_' . Str::random(10) . '.' . $request->file('image')->getClientOriginalExtension();
            $request->image->move(public_path('upload/' . date('Y') . '/' . date('m') . '/' . date('d')), $imageName);
            $album->image = $imageName;
        }

        $album->save();
        $imagePath = 'upload/' . date('Y') . '/' . date('m') . '/' . date('d') . '/' . $imageName;
        $imageUrl = url($imagePath);

        $response = [
            'message' => 'Album created successfully',
            'album_id' => $album->id,
            'image_path' => $imagePath,
            'image_url' => $imageUrl,
            'image_name' => $imageName,
        ];
        return response()->json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $album = Album::findOrFail($id);
        $imagePath = $album->image ? 'upload/' . date('Y', strtotime($album->created_at)) . '/' . date('m', strtotime($album->created_at)) . '/' . date('d', strtotime($album->created_at)) . '/' . $album->image : null;
        $imageUrl = $imagePath ? url($imagePath) : null;
        $response = [
            'album' => [
                'id' => $album->id,
                'image_path' => $imagePath,
                'image_url' => $imageUrl,
                'image_name' => $album->image,
                'created_at' => $album->created_at,
                'updated_at' => $album->updated_at,
            ],
        ];

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:png,jpg,svg|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Image validation failed', 'errors' => $validator->errors()], 400);
        }

        $album = Album::findOrFail($id);

        if ($request->hasFile('image')) {
            if ($album->image) {
                $imagePath = public_path('upload/' . date('Y', strtotime($album->created_at)) . '/' . date('m', strtotime($album->created_at)) . '/' . date('d', strtotime($album->created_at)) . '/' . $album->image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            // $imageHash = substr(md5(uniqid()), 0, 2);
            // $imageName = date('Ymd') . $imageHash . '.' . $request->file('image')->getClientOriginalExtension();
            $imageName = date('Ymd') . '_' . Str::random(10) . '.' . $request->file('image')->getClientOriginalExtension();

            $request->file('image')->move(public_path('upload/' . date('Y', strtotime($album->created_at)) . '/' . date('m', strtotime($album->created_at)) . '/' . date('d', strtotime($album->created_at))), $imageName);
            $album->image = $imageName;
        }

        $album->save();
        $imagePath = $album->image ? 'upload/' . date('Y', strtotime($album->created_at)) . '/' . date('m', strtotime($album->created_at)) . '/' . date('d', strtotime($album->created_at)) . '/' . $album->image : null;
        $imageUrl = $imagePath ? url($imagePath) : null;

        $response = [
            'message' => 'album updated successfully',
            'album_id' => $album->id,
            'image_path' => $imagePath,
            'image_url' => $imageUrl,
            'image_name' => $album->image,
        ];

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $album = Album::findOrFail($id);

        if ($album->image) {
            $imagePath = public_path('upload/' . date('Y', strtotime($album->created_at)) . '/' . date('m', strtotime($album->created_at)) . '/' . date('d', strtotime($album->created_at)) . '/' . $album->image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $album->delete();

        return response()->json(['message' => 'album deleted successfully']);
    }
}
