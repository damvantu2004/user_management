<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(name="Users", description="Operations about users")
 * 
 * @OA\Schema(
 *     schema="PaginationLinks",
 *     type="object",
 *     @OA\Property(property="first", type="string"),
 *     @OA\Property(property="last", type="string"),
 *     @OA\Property(property="prev", type="string", nullable=true),
 *     @OA\Property(property="next", type="string", nullable=true)
 * )
 * 
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     type="object",
 *     @OA\Property(property="current_page", type="integer"),
 *     @OA\Property(property="per_page", type="integer"),
 *     @OA\Property(property="total", type="integer")
 * )
 */
class UserController extends Controller
{
    /**
     * Hiển thị danh sách tất cả người dùng
     * 
     * @return \Illuminate\Http\JsonResponse
     * 
     * @OA\Get(
     *     path="/api/users",
     *     summary="Lấy danh sách người dùng",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", description="Trang số", @OA\Schema(type="integer", default=1)),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách người dùng phân trang",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
     *             @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
     *             @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
     *         )
     *     )
     * )
     */
    public function index()
    {
        // Lấy tất cả user và phân trang kết quả
        $users = User::paginate(10);
        return $this->successResponse($users, 'Lấy danh sách người dùng thành công');
    }

    /**
     * Lưu trữ người dùng mới vào database
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @OA\Post(
     *     path="/api/users",
     *     summary="Tạo người dùng mới",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="Nguyễn Văn B"),
     *             @OA\Property(property="email", type="string", format="email", example="newuser@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="role", type="string", enum={"admin","user"}, example="user"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Tạo người dùng thành công", @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=422, description="Dữ liệu không hợp lệ")
     * )
     */
    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'sometimes|in:admin,user',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Tạo người dùng mới
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role ?? 'user',
            'is_active' => $request->is_active ?? true,
        ]);

        return $this->createdResponse($user, 'Tạo người dùng thành công');
    }

    /**
     * Hiển thị thông tin chi tiết của một người dùng
     * 
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     * 
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Lấy thông tin chi tiết người dùng",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Thông tin người dùng", @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=404, description="Không tìm thấy người dùng")
     * )
     */
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->notFoundResponse('Không tìm thấy người dùng');
        }
        
        return $this->successResponse($user, 'Lấy thông tin người dùng thành công');
    }

    /**
     * Cập nhật thông tin người dùng
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     * 
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Cập nhật thông tin người dùng",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Name"),
     *             @OA\Property(property="email", type="string", format="email", example="updated@example.com"),
     *             @OA\Property(property="role", type="string", enum={"admin","user"}, example="admin"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Cập nhật thành công", @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=404, description="Không tìm thấy người dùng"),
     *     @OA\Response(response=422, description="Dữ liệu không hợp lệ")
     * )
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->notFoundResponse('Không tìm thấy người dùng');
        }

        // Validate dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'sometimes|in:admin,user',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Cập nhật thông tin người dùng
        $user->update($request->only(['name', 'email', 'role', 'is_active']));

        return $this->successResponse($user, 'Cập nhật thông tin người dùng thành công');
    }

    /**
     * Xóa người dùng khỏi hệ thống
     * 
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     * 
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Xóa người dùng",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Xóa thành công"),
     *     @OA\Response(response=404, description="Không tìm thấy người dùng")
     * )
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->notFoundResponse('Không tìm thấy người dùng');
        }

        $user->delete();

        return $this->successResponse(null, 'Xóa người dùng thành công', 200);
    }
}

