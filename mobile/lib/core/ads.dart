import 'package:flutter/material.dart';
import 'package:google_mobile_ads/google_mobile_ads.dart';

/// AdMob reklama boshqaruvi.
///
/// MUHIM — daromad olish uchun:
/// 1. https://apps.admob.com da ilova qo'shing va real APP ID oling
///    (AndroidManifest.xml'dagi `ca-app-pub-3940256099942544~3347511713`
///    test ID'sini o'z ID'ingizga almastiring)
/// 2. Banner va Interstitial unit ID'larini yarating va shu fayldagi
///    [_adUnitBanner] / [_adUnitInterstitial] o'rniga qo'ying
/// 3. Play Store'da chiqarishdan OLDIN test ID'lar ishlatilsa — hisob
///    bloklanishi mumkin; test qurilmalaringizni AdMob'da ro'yxatdan o'tkazing
///
/// Hozirgi qiymatlar Google'ning rasmiy TEST ID'lari — reklama ko'rinadi,
/// lekin daromad hisoblanmaydi (xavfsiz sinov uchun).
class AdService {
  AdService._();

  static bool _initialized = false;

  // ---- Google TEST ad unit ID'lari (daromad bermaydi) ----
  static const String _adUnitBanner =
      'ca-app-pub-3940256099942544/6300978111';
  static const String _adUnitInterstitial =
      'ca-app-pub-3940256099942544/1033173712';

  static BannerAd? _banner;
  static InterstitialAd? _interstitial;
  static bool _interstitialLoading = false;
  static int _pdfCountSinceAd = 0;

  /// AdMob SDK'ni ishga tushirish (bir marta).
  static Future<void> ensureInitialized() async {
    if (_initialized) {
      return;
    }

    _initialized = true;

    try {
      await MobileAds.instance.initialize();
      _loadInterstitial();
    } catch (_) {
      // Reklama yuklanmasa ilova baribir ishlashi kerak.
    }
  }

  // ------------------------------------------------------------------
  //  Banner (Home ekran pastida)
  // ------------------------------------------------------------------

  /// Yuklangan banner — null bo'lsa reklamani umuman ko'rsatmaymiz.
  static BannerAd? get banner => _banner;

  static void loadBanner() {
    if (_banner != null) {
      return;
    }

    final ad = BannerAd(
      adUnitId: _adUnitBanner,
      size: AdSize.banner,
      request: const AdRequest(),
      listener: BannerAdListener(
        onAdLoaded: (ad) {
          _banner = ad as BannerAd;
        },
        onAdFailedToLoad: (ad, error) {
          ad.dispose();
          _banner = null;
        },
      ),
    );

    ad.load();
  }

  // ------------------------------------------------------------------
  //  Interstitial (har 3 ta PDF'dan keyin, PDF olingach)
  // ------------------------------------------------------------------

  /// PDF yaratildi — hisoblagichni oshiradi, kerak bo'lsa reklama ko'rsatadi.
  static void onPdfGenerated(BuildContext context) {
    _pdfCountSinceAd++;

    if (_pdfCountSinceAd >= 3) {
      _pdfCountSinceAd = 0;
      _showInterstitial();
    }

    // Keyingisi uchun oldindan yuklab qo'yamiz.
    _loadInterstitial();
  }

  static void _loadInterstitial() {
    if (_interstitial != null || _interstitialLoading) {
      return;
    }

    _interstitialLoading = true;

    InterstitialAd.load(
      adUnitId: _adUnitInterstitial,
      request: const AdRequest(),
      adLoadCallback: InterstitialAdLoadCallback(
        onAdLoaded: (ad) {
          _interstitial = ad;
          _interstitialLoading = false;
        },
        onAdFailedToLoad: (error) {
          _interstitial = null;
          _interstitialLoading = false;
        },
      ),
    );
  }

  static void _showInterstitial() {
    final ad = _interstitial;

    if (ad == null) {
      return;
    }

    ad.fullScreenContentCallback = FullScreenContentCallback(
      onAdDismissedFullScreenContent: (ad) {
        ad.dispose();
        _interstitial = null;
        _loadInterstitial();
      },
      onAdFailedToShowFullScreenContent: (ad, error) {
        ad.dispose();
        _interstitial = null;
      },
    );

    ad.show();
    _interstitial = null;
  }

  /// Ilova yopilganda xotirani tozalash (zaruriy emas, lekin toza).
  static void dispose() {
    _banner?.dispose();
    _banner = null;
    _interstitial?.dispose();
    _interstitial = null;
  }
}

/// Home ekran pastidagi banner reklama.
/// Reklama yuklanmagan bo'lsa — bo'sh joy egallamaydi (SizedBox.shrink).
class AdBanner extends StatefulWidget {
  const AdBanner({super.key});

  @override
  State<AdBanner> createState() => _AdBannerState();
}

class _AdBannerState extends State<AdBanner> {
  @override
  void initState() {
    super.initState();
    AdService.loadBanner();
    AdService.banner?.setImmersiveMode(false);
  }

  @override
  void dispose() {
    // Banner App'ning umrining oxirigacha yashaydi — bu yerdan dispose
    // qilmaymiz (Home ekran qayta qurilganda qayta yuklanishi oldini olamiz).
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final banner = AdService.banner;

    if (banner == null) {
      return const SizedBox.shrink();
    }

    return SafeArea(
      top: false,
      child: Container(
        alignment: Alignment.center,
        width: banner.size.width.toDouble(),
        height: banner.size.height.toDouble(),
        child: AdWidget(ad: banner),
      ),
    );
  }
}
