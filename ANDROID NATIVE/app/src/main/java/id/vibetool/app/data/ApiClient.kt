package id.vibetool.app.data

import android.content.Context
import okhttp3.Interceptor
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit

/**
 * Singleton Retrofit client.
 *
 * PENTING: untuk testing lokal (php artisan serve) dari emulator, ganti
 * BASE_URL menjadi "http://10.0.2.2:8000/" dan izinkan cleartext di manifest.
 */
object ApiClient {

    const val BASE_URL = "https://vibetool.id/"

    @Volatile
    private var service: ApiService? = null
    private lateinit var tokenStore: TokenStore

    fun init(context: Context) {
        tokenStore = TokenStore(context.applicationContext)
    }

    fun tokens(): TokenStore = tokenStore

    fun api(): ApiService {
        return service ?: synchronized(this) {
            service ?: buildService().also { service = it }
        }
    }

    private fun buildService(): ApiService {
        val authInterceptor = Interceptor { chain ->
            val builder = chain.request().newBuilder()
                .addHeader("Accept", "application/json")
            tokenStore.token?.let { builder.addHeader("Authorization", "Bearer $it") }
            chain.proceed(builder.build())
        }

        val logging = HttpLoggingInterceptor().apply {
            level = HttpLoggingInterceptor.Level.BASIC
        }

        val client = OkHttpClient.Builder()
            .addInterceptor(authInterceptor)
            .addInterceptor(logging)
            .connectTimeout(20, TimeUnit.SECONDS)
            .readTimeout(30, TimeUnit.SECONDS)
            .build()

        return Retrofit.Builder()
            .baseUrl(BASE_URL)
            .client(client)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)
    }
}
