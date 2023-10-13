<?php

namespace App\Http\Controllers;
use App\Models\Bookings;
use App\Models\Services;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ServiceController extends Controller
{
    public function insertServices(Request $request)
    {
        try {
            $user = auth()->user();

            $validatedData = $request->validate([
                'services.*.description' => 'required|string',
                'services.*.price' => 'required|numeric',
                'services.*.show_price' => 'string',
                'services.*.image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Get the array of services from the request
            $servicesData = $request->input('services');

            if (!is_array($servicesData) || empty($servicesData)) {
                return response()->json(['success' => false, 'message' => 'No services data received.'], 400);
            }

            $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');

            // Process each service
            $createdServices = [];
            foreach ($servicesData as $data) {
                // Find the service by description and user_id
                $service = Services::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'description' => $data['description'],
                    ],
                    [
                        'price' => $data['price'],
                        'show_price' => $data['show_price'] == "true" ? true : false,
                        'created_at' => $currentDateTime,
                        'updated_at' => $currentDateTime,
                    ]
                );

                // Handle image upload if necessary
                if ($request->hasFile('image')) {
                    $imageFile = $request->file('image');
                    $imageName = time() . '.' . $imageFile->getClientOriginalExtension();
                    $imageFile->move(public_path('uploads'), $imageName);
                    $service->image = $imageName;
                    $service->save();
                }

                $createdServices[] = $service;
            }

            $allServices = Services::where('user_id', $user->id)->get();

            return response()->json([
                'success' => true,
                'message' => 'Services created/updated successfully',
                'data' => $allServices,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function getUserServices(Request $request) {
        try {
            if (empty($request->user_id)) {
                return response()->json(['success' => false, 'message' => 'user_id is required'], 400);
            }

            $myServices = Services::where('user_id', $request->user_id)->get();

            if(!empty($myServices)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Services retrieved successfully',
                    'data' => $myServices,
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'No services found',
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function createBooking(Request $request)
    {
        try {
            $user = auth()->user();

            $validatedData = $request->validate([
                'freelancer_id' => 'required|exists:users,id', // Assuming 'freelancer_id' is a valid user ID
                'service_id' => 'required|exists:services,id', // Assuming 'service_id' is a valid service ID
                'service_amount' => 'required|numeric',
            ]);

            $freelancerId = $request->input('freelancer_id');
            $serviceId = $request->input('service_id');
            $serviceAmount = $request->input('service_amount');

            // Fetch the freelancer's data
            $freelancer = User::find($freelancerId);

            if (!$freelancer) {
                return response()->json(['success' => false, 'message' => 'Freelancer not found'], 404);
            }

            $bookingData = [
                'user_id' => $user->id,
                'freelancer_id' => $freelancerId,
                'service_id' => $serviceId,
                'service_amount' => $serviceAmount,
            ];

            $booking = Bookings::create($bookingData);

            // Include the "freelancer" data in the "booking" object
            $booking->freelancer = $freelancer;

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => $booking,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }


    public function getBookingsByUserId($user_id)
    {
        try {
            $bookings = Bookings::with('freelancer')
                ->where('user_id', $user_id)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Bookings retrieved successfully',
                'data' => $bookings,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }


}
