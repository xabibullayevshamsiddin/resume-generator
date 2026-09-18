import 'package:flutter/material.dart';
import 'package:video_player/video_player.dart';

import '../core/api_config.dart';
import '../core/api_client.dart';
import '../core/theme.dart';

/// Video qo'llanma — serverdan to'g'ridan-to'g'ri stream qilinadi
/// (`/videos/qollanma.mp4`), ilova ichiga o'rnatilmaydi.
class VideoScreen extends StatefulWidget {
  const VideoScreen({super.key});

  @override
  State<VideoScreen> createState() => _VideoScreenState();
}

class _VideoScreenState extends State<VideoScreen> {
  late final VideoPlayerController _controller;
  bool _initialized = false;
  String? _error;

  @override
  void initState() {
    super.initState();

    _controller = VideoPlayerController.networkUrl(Uri.parse(videoUrl()))
      ..initialize().then((_) {
        if (!mounted) {
          return;
        }

        setState(() => _initialized = true);
        _controller.play();
      }).catchError((Object e) {
        if (!mounted) {
          return;
        }

        setState(() => _error = ApiClient.friendlyError(e));
      });
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: AppColors.ink,
        foregroundColor: AppColors.parchment,
        title: const Text('Video qo\'llanma'),
      ),
      body: Center(
        child: _buildBody(),
      ),
    );
  }

  Widget _buildBody() {
    if (_error != null) {
      return Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.wifi_off, size: 48, color: AppColors.slate),
            const SizedBox(height: 16),
            Text(
              _error!,
              textAlign: TextAlign.center,
              style: const TextStyle(color: AppColors.parchment),
            ),
          ],
        ),
      );
    }

    if (!_initialized) {
      return const CircularProgressIndicator(color: AppColors.brass);
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          AspectRatio(
            aspectRatio: _controller.value.aspectRatio,
            child: VideoPlayer(_controller),
          ),
          const SizedBox(height: 12),
          VideoProgressIndicator(
            _controller,
            allowScrubbing: true,
            colors: const VideoProgressColors(
              playedColor: AppColors.brass,
              bufferedColor: AppColors.slate,
              backgroundColor: Colors.white24,
            ),
          ),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              IconButton(
                onPressed: () {
                  setState(() {
                    _controller.value.isPlaying
                        ? _controller.pause()
                        : _controller.play();
                  });
                },
                icon: Icon(
                  _controller.value.isPlaying ? Icons.pause : Icons.play_arrow,
                  color: AppColors.parchment,
                  size: 32,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
