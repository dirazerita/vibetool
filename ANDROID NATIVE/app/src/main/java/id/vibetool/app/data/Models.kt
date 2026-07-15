package id.vibetool.app.data

import com.google.gson.annotations.SerializedName

// ===== Auth =====

data class LoginRequest(val email: String, val password: String)

data class LoginResponse(
    val ok: Boolean,
    val token: String? = null,
    val user: User? = null,
    val message: String? = null,
)

data class RegisterRequest(
    val name: String,
    val email: String,
    @SerializedName("whatsapp_number") val whatsappNumber: String?,
    val password: String,
    @SerializedName("password_confirmation") val passwordConfirmation: String,
)

data class SimpleResponse(val ok: Boolean, val message: String? = null)

data class MeResponse(val ok: Boolean, val user: User? = null)

data class User(
    val id: Long,
    val name: String,
    val email: String,
    @SerializedName("whatsapp_number") val whatsappNumber: String?,
    @SerializedName("referral_code") val referralCode: String?,
    val balance: Double,
    @SerializedName("profile_photo") val profilePhoto: String?,
    @SerializedName("can_upload_product") val canUploadProduct: Boolean,
)

// ===== Produk =====

data class ProductsResponse(val ok: Boolean, val products: List<Product> = emptyList())

data class ProductResponse(val ok: Boolean, val product: Product? = null, val message: String? = null)

data class Product(
    val id: Long,
    val slug: String,
    val title: String,
    val description: String,
    val price: Double,
    @SerializedName("compare_at_price") val compareAtPrice: Double?,
    @SerializedName("is_free") val isFree: Boolean,
    @SerializedName("has_packages") val hasPackages: Boolean,
    @SerializedName("product_type") val productType: String?,
    val thumbnail: String?,
    @SerializedName("web_url") val webUrl: String?,
    val packages: List<ProductPackage>? = null,
)

data class ProductPackage(
    val id: Long,
    val name: String,
    val price: Double,
    @SerializedName("duration_type") val durationType: String?,
)

// ===== Dashboard =====

data class DashboardResponse(
    val ok: Boolean,
    val summary: DashboardSummary? = null,
    @SerializedName("recent_commissions") val recentCommissions: List<Commission> = emptyList(),
)

data class DashboardSummary(
    val balance: Double,
    @SerializedName("total_commission") val totalCommission: Double,
    @SerializedName("team_count") val teamCount: Int,
    @SerializedName("purchase_count") val purchaseCount: Int,
    @SerializedName("referral_code") val referralCode: String?,
    @SerializedName("referral_link") val referralLink: String?,
)

// ===== Komisi =====

data class CommissionsResponse(
    val ok: Boolean,
    val commissions: List<Commission> = emptyList(),
    @SerializedName("has_more") val hasMore: Boolean = false,
)

data class Commission(
    val id: Long,
    val type: String,
    val amount: Double,
    val status: String,
    @SerializedName("product_title") val productTitle: String?,
    @SerializedName("created_at") val createdAt: String?,
)

// ===== Lisensi =====

data class LicensesResponse(val ok: Boolean, val licenses: List<License> = emptyList())

data class License(
    val id: Long,
    val key: String,
    @SerializedName("product_title") val productTitle: String,
    @SerializedName("is_lifetime") val isLifetime: Boolean,
    @SerializedName("is_expired") val isExpired: Boolean,
    @SerializedName("expires_at") val expiresAt: String?,
    @SerializedName("max_devices") val maxDevices: Int,
    val devices: List<LicenseDevice> = emptyList(),
)

data class LicenseDevice(
    val label: String?,
    @SerializedName("last_seen_at") val lastSeenAt: String?,
)

// ===== Tim =====

data class TeamResponse(val ok: Boolean, val team: List<TeamMember> = emptyList())

data class TeamMember(
    val id: Long,
    val name: String,
    val status: String?,
    @SerializedName("joined_at") val joinedAt: String?,
)

// ===== Pembelian =====

data class PurchasesResponse(val ok: Boolean, val purchases: List<Purchase> = emptyList())

data class Purchase(
    val id: Long,
    @SerializedName("product_title") val productTitle: String,
    val amount: Double,
    val status: String,
    @SerializedName("created_at") val createdAt: String?,
    @SerializedName("download_url") val downloadUrl: String?,
)

// ===== Checkout =====

data class CheckoutLinkResponse(
    val ok: Boolean,
    @SerializedName("checkout_url") val checkoutUrl: String? = null,
    val message: String? = null,
)
