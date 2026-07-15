package id.vibetool.app.data

import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Path
import retrofit2.http.Query

interface ApiService {

    // ===== Publik =====
    @POST("api/app/login")
    suspend fun login(@Body body: LoginRequest): Response<LoginResponse>

    @POST("api/auth/register")
    suspend fun register(@Body body: RegisterRequest): Response<SimpleResponse>

    @GET("api/app/products")
    suspend fun products(): Response<ProductsResponse>

    @GET("api/app/products/{slug}")
    suspend fun product(@Path("slug") slug: String): Response<ProductResponse>

    // ===== Auth (Bearer token via interceptor) =====
    @POST("api/app/logout")
    suspend fun logout(): Response<SimpleResponse>

    @GET("api/app/me")
    suspend fun me(): Response<MeResponse>

    @GET("api/app/dashboard")
    suspend fun dashboard(): Response<DashboardResponse>

    @GET("api/app/licenses")
    suspend fun licenses(): Response<LicensesResponse>

    @POST("api/app/licenses/{id}/reset-devices")
    suspend fun resetLicenseDevices(@Path("id") licenseId: Long): Response<SimpleResponse>

    @GET("api/app/commissions")
    suspend fun commissions(): Response<CommissionsResponse>

    @GET("api/app/team")
    suspend fun team(): Response<TeamResponse>

    @GET("api/app/purchases")
    suspend fun purchases(): Response<PurchasesResponse>

    @GET("api/app/checkout-link/{slug}")
    suspend fun checkoutLink(@Path("slug") slug: String): Response<CheckoutLinkResponse>

    /** Signed autologin URL untuk membuka halaman dashboard web dari app. */
    @GET("api/app/web-link")
    suspend fun webLink(@Query("to") to: String): Response<WebLinkResponse>
}
