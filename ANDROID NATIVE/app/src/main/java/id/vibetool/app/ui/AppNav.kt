package id.vibetool.app.ui

import androidx.compose.foundation.layout.padding
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Home
import androidx.compose.material.icons.filled.Key
import androidx.compose.material.icons.filled.Paid
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.Group
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.NavigationBar
import androidx.compose.material3.NavigationBarItem
import androidx.compose.material3.NavigationBarItemDefaults
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.navigation.NavGraph.Companion.findStartDestination
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.currentBackStackEntryAsState
import androidx.navigation.compose.rememberNavController
import id.vibetool.app.data.ApiClient
import id.vibetool.app.ui.screens.CommissionsScreen
import id.vibetool.app.ui.screens.HomeScreen
import id.vibetool.app.ui.screens.LicensesScreen
import id.vibetool.app.ui.screens.LoginScreen
import id.vibetool.app.ui.screens.ProductDetailScreen
import id.vibetool.app.ui.screens.ProfileScreen
import id.vibetool.app.ui.screens.PurchasesScreen
import id.vibetool.app.ui.screens.RegisterScreen
import id.vibetool.app.ui.screens.TeamScreen
import id.vibetool.app.ui.theme.BgDeep
import id.vibetool.app.ui.theme.Indigo
import id.vibetool.app.ui.theme.Surface1
import id.vibetool.app.ui.theme.TextMuted

private data class BottomTab(
    val route: String,
    val label: String,
    val icon: ImageVector,
)

private val bottomTabs = listOf(
    BottomTab("home", "Beranda", Icons.Filled.Home),
    BottomTab("commissions", "Komisi", Icons.Filled.Paid),
    BottomTab("licenses", "Lisensi", Icons.Filled.Key),
    BottomTab("team", "Tim", Icons.Filled.Group),
    BottomTab("profile", "Profil", Icons.Filled.Person),
)

@Composable
fun AppNav() {
    val navController = rememberNavController()
    val backStack by navController.currentBackStackEntryAsState()
    val currentRoute = backStack?.destination?.route

    val isLoggedIn = ApiClient.tokens().isLoggedIn()
    val showBottomBar = currentRoute in bottomTabs.map { it.route }

    Scaffold(
        containerColor = BgDeep,
        bottomBar = {
            if (showBottomBar) {
                NavigationBar(containerColor = Surface1) {
                    bottomTabs.forEach { tab ->
                        NavigationBarItem(
                            selected = currentRoute == tab.route,
                            onClick = {
                                navController.navigate(tab.route) {
                                    popUpTo(navController.graph.findStartDestination().id) {
                                        saveState = true
                                    }
                                    launchSingleTop = true
                                    restoreState = true
                                }
                            },
                            icon = { Icon(tab.icon, contentDescription = tab.label) },
                            label = { Text(tab.label, style = MaterialTheme.typography.labelSmall) },
                            colors = NavigationBarItemDefaults.colors(
                                selectedIconColor = Color.White,
                                selectedTextColor = Color.White,
                                indicatorColor = Indigo,
                                unselectedIconColor = TextMuted,
                                unselectedTextColor = TextMuted,
                            ),
                        )
                    }
                }
            }
        },
    ) { padding ->
        NavHost(
            navController = navController,
            startDestination = if (isLoggedIn) "home" else "login",
            modifier = Modifier.padding(padding),
        ) {
            composable("login") {
                LoginScreen(
                    onLoginSuccess = {
                        navController.navigate("home") {
                            popUpTo("login") { inclusive = true }
                        }
                    },
                    onGoRegister = { navController.navigate("register") },
                )
            }
            composable("register") {
                RegisterScreen(onBackToLogin = { navController.popBackStack() })
            }
            composable("home") {
                HomeScreen(
                    onOpenProduct = { slug -> navController.navigate("product/$slug") },
                    onOpenTeam = {
                        navController.navigate("team") {
                            popUpTo(navController.graph.findStartDestination().id) { saveState = true }
                            launchSingleTop = true
                            restoreState = true
                        }
                    },
                    onOpenPurchases = { navController.navigate("purchases") },
                )
            }
            composable("purchases") {
                PurchasesScreen(onBack = { navController.popBackStack() })
            }
            composable("product/{slug}") { entry ->
                val slug = entry.arguments?.getString("slug") ?: return@composable
                ProductDetailScreen(slug = slug, onBack = { navController.popBackStack() })
            }
            composable("commissions") { CommissionsScreen() }
            composable("licenses") { LicensesScreen() }
            composable("team") { TeamScreen() }
            composable("profile") {
                ProfileScreen(
                    onLoggedOut = {
                        navController.navigate("login") {
                            popUpTo(0) { inclusive = true }
                        }
                    },
                    onOpenPurchases = { navController.navigate("purchases") },
                )
            }
        }
    }
}
