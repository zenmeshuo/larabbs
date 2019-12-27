<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TopicRequest;
use App\Models\Category;
use Auth;
use App\Handlers\ImageUploadHandler;
use App\Models\User;

class TopicsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

	public function index(Request $request, Topic $topic, User $user)
	{
		$topics = $topic->withOrder($request->order)->with('user', 'category')->paginate();
        $active_users = $user->getActiveUsers();
		return view('topics.index', compact('topics', 'active_users'));
	}

    public function show(Request $request, Topic $topic)
    {
        if (isset($topic->slug) && $topic->slug != $request->slug) {
            return redirect($topic->link(), 301);
        }
        return view('topics.show', compact('topic'));
    }

	public function create(Topic $topic)
	{
        $categories = Category::all();
		return view('topics.create_and_edit', compact('topic', 'categories'));
	}

	public function store(TopicRequest $request, Topic $topic)
	{
		$topic = $topic->fill($request->all());
        $topic->user_id = Auth::id();
        $topic->save();

		return redirect()->to($topic->link())->with('success', '帖子创建成功！');
	}

	public function edit(Topic $topic)
	{
        try {
            $this->authorize('update', $topic);
        } catch (AuthorizationException $e) {
            return abort(403, '无权访问！');
        }
        $categories = Category::all();
		return view('topics.create_and_edit', compact('topic', 'categories'));
	}

	public function update(TopicRequest $request, Topic $topic)
	{
        try {
            $this->authorize('update', $topic);
        } catch (AuthorizationException $e) {
            return abort(403, '无权访问！');
        }
		$topic->update($request->all());

		return redirect()->to($topic->link())->with('message', 'Updated successfully.');
	}

	public function destroy(Topic $topic)
	{
        try {
            $this->authorize('destroy', $topic);
        } catch (AuthorizationException $e) {
            return abort(403, '无权访问！');
        }
		$topic->delete();

		return redirect()->route('topics.index')->with('message', 'Deleted successfully.');
	}

    public function uploadImage(Request $request, ImageUploadHandler $uploader)
    {
        // 初始化返回数据，默认失败
        $data = [
            'success' => false,
            'msg' => '上传失败！',
            'file_path' => ''
        ];

        // 判断是否有上传文件，并赋值给 $file
        if ($file = $request->upload_file) {
            // 保存图片到本地
            $result = $uploader->save($file, 'topics', \Auth::id(), 1024);
            // 图片保存成功
            if ($result) {
                $data = [
                    'success' => true,
                    'msg' => '上传成功！',
                    'file_path' => $result['path']
                ];
            }
        }

        return $data;
    }
}
