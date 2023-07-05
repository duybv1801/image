<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


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


    //Validation
    private function validateAlbum(Request $request)
    {
        return Validator::make($request->all(), [
            'image' => 'image|mimes:png,jpg,svg|max:10240',
        ]);
    }

    private function generateImagePath($album, $timestamp = null)
    {
        $timestamp = $timestamp ?: $album->updated_at;
        return 'public/upload/' . date('Y/m/d/', strtotime($timestamp)) . $album->image;
    }

    private function generateImageName($extension)
    {
        return date('Ymd') . '_' . Str::random(10) . '.' . $extension;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validateAlbum($request);
        $image = $request->image;

        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => 'Image validation failed',
                    'errors' => $validator->errors()
                ],
                400
            );
        }

        $album = new Album();
        if ($image) {
            $imageName = $this->generateImageName($image->extension());
            $imagePath = $image->storeAs('public/upload/' . date('Y/m/d'), $imageName);
            $album->image = $imageName;
        }

        $album->save();
        $imageUrl = Storage::url($imagePath);



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
        $imagePath = $album->image ? $this->generateImagePath($album) : null;
        $imageUrl = $imagePath ? url($imagePath) : null;
        $response = [
            'album' => $album,
            'image_path' => $imagePath,
            'image_url' => $imageUrl,
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
        $validator = $this->validateAlbum($request);
        $image = $request->image;

        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => 'Image validation failed',
                    'errors' => $validator->errors()
                ],
                400
            );
        }

        $album = Album::findOrFail($id);

        if ($image) {
            if ($album->image) {
                $imagePath = $this->generateImagePath($album);
                if (Storage::exists($imagePath)) {
                    Storage::delete($imagePath);
                }
            }

            $imageName = $this->generateImageName($image->extension());

            $imagePath = $image->storeAs('public/upload/' . date('Y/m/d'), $imageName);

            $album->image = $imageName;
        }

        $album->save();
        $imageUrl = Storage::url($imagePath);


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
            $imagePath = $this->generateImagePath($album);
            if (Storage::exists($imagePath)) {
                Storage::delete($imagePath);
            }
        }

        $album->delete();

        return response()->json([
            'message' => 'album deleted successfully'
        ]);
    }
}
