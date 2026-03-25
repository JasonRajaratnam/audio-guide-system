// Audio player functionality
class AudioPlayer {
    constructor() {
        this.audio = null;
        this.isPlaying = false;
        this.token = this.getTokenFromUrl();
        this.init();
    }

    getTokenFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('token');
    }

    init() {
        // Get elements
        this.playBtn = document.getElementById('playBtn');
        this.rewindBtn = document.getElementById('rewindBtn');
        this.forwardBtn = document.getElementById('forwardBtn');
        this.progressBar = document.getElementById('progressBar');
        this.progressFill = document.getElementById('progressFill');
        this.waveformProgress = document.getElementById('waveformProgress');
        this.currentTimeEl = document.getElementById('currentTime');
        this.durationEl = document.getElementById('duration');
        this.volumeSlider = document.getElementById('volumeSlider');
        this.volumeFill = document.getElementById('volumeFill');
        this.volumeIcon = document.getElementById('volumeIcon');

        // Create audio element
        const streamUrl = `download.php?token=${this.token}&action=stream`;
        this.audio = new Audio(streamUrl);

        // Set initial volume
        this.audio.volume = 1.0;

        // Event listeners
        this.playBtn.addEventListener('click', () => this.togglePlay());
        this.rewindBtn.addEventListener('click', () => this.skip(-10));
        this.forwardBtn.addEventListener('click', () => this.skip(10));
        this.progressBar.addEventListener('click', (e) => this.seek(e));
        this.volumeSlider.addEventListener('click', (e) => this.changeVolume(e));

        // Audio events
        this.audio.addEventListener('loadedmetadata', () => this.onLoadedMetadata());
        this.audio.addEventListener('timeupdate', () => this.onTimeUpdate());
        this.audio.addEventListener('ended', () => this.onEnded());
        this.audio.addEventListener('error', (e) => this.onError(e));

        console.log('Audio player initialized');
    }

    togglePlay() {
        if (this.isPlaying) {
            this.pause();
        } else {
            this.play();
        }
    }

    play() {
        this.audio.play()
            .then(() => {
                this.isPlaying = true;
                this.playBtn.innerHTML = '⏸';
                console.log('Playing audio');
            })
            .catch(error => {
                console.error('Play error:', error);
                alert('Error playing audio. Please try again.');
            });
    }

    pause() {
        this.audio.pause();
        this.isPlaying = false;
        this.playBtn.innerHTML = '▶';
        console.log('Audio paused');
    }

    skip(seconds) {
        this.audio.currentTime += seconds;
    }

    seek(e) {
        const rect = this.progressBar.getBoundingClientRect();
        const percent = (e.clientX - rect.left) / rect.width;
        this.audio.currentTime = percent * this.audio.duration;
    }

    changeVolume(e) {
        const rect = this.volumeSlider.getBoundingClientRect();
        const percent = (e.clientX - rect.left) / rect.width;
        this.audio.volume = Math.max(0, Math.min(1, percent));
        this.updateVolumeDisplay();
    }

    updateVolumeDisplay() {
        const volume = this.audio.volume;
        this.volumeFill.style.width = (volume * 100) + '%';

        // Update icon
        if (volume === 0) {
            this.volumeIcon.innerHTML = '🔇';
        } else if (volume < 0.5) {
            this.volumeIcon.innerHTML = '🔉';
        } else {
            this.volumeIcon.innerHTML = '🔊';
        }
    }

    onLoadedMetadata() {
        const duration = this.formatTime(this.audio.duration);
        this.durationEl.textContent = duration;
        console.log('Audio loaded, duration:', duration);
    }

    onTimeUpdate() {
        const current = this.audio.currentTime;
        const duration = this.audio.duration;
        const percent = (current / duration) * 100;

        this.progressFill.style.width = percent + '%';
        this.waveformProgress.style.width = percent + '%';
        this.currentTimeEl.textContent = this.formatTime(current);
    }

    onEnded() {
        this.isPlaying = false;
        this.playBtn.innerHTML = '▶';
        this.audio.currentTime = 0;
        console.log('Audio ended');
    }

    onError(e) {
        console.error('Audio error:', e);
        alert('Error loading audio. Please refresh the page or contact support.');
    }

    formatTime(seconds) {
        if (isNaN(seconds)) return '0:00';
        
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return mins + ':' + (secs < 10 ? '0' : '') + secs;
    }
}

// Initialize player when page loads
document.addEventListener('DOMContentLoaded', () => {
    // Check if we're on the player page (has audio player elements)
    if (document.getElementById('playBtn')) {
        new AudioPlayer();
    }
});

// Download button handler
function downloadAudio() {
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');
    const downloadUrl = `download.php?token=${token}&action=download`;
    
    // Create temporary link and trigger download
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    console.log('Download started');
}