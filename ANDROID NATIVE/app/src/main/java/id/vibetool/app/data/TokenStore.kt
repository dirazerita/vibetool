package id.vibetool.app.data

import android.content.Context
import android.content.SharedPreferences

/**
 * Penyimpanan token API sederhana berbasis SharedPreferences.
 */
class TokenStore(context: Context) {

    private val prefs: SharedPreferences =
        context.getSharedPreferences("vibetool_auth", Context.MODE_PRIVATE)

    var token: String?
        get() = prefs.getString(KEY_TOKEN, null)
        set(value) = prefs.edit().putString(KEY_TOKEN, value).apply()

    var userName: String?
        get() = prefs.getString(KEY_NAME, null)
        set(value) = prefs.edit().putString(KEY_NAME, value).apply()

    fun isLoggedIn(): Boolean = !token.isNullOrEmpty()

    fun clear() {
        prefs.edit().clear().apply()
    }

    companion object {
        private const val KEY_TOKEN = "api_token"
        private const val KEY_NAME = "user_name"
    }
}
