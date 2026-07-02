// Sound effects using Web Audio API

let audioContext: AudioContext | null = null;

function getAudioContext(): AudioContext {
  if (!audioContext) {
    audioContext = new (window.AudioContext || (window as unknown as { webkitAudioContext: typeof AudioContext }).webkitAudioContext)();
  }
  return audioContext;
}

// Play a beep sound with specified frequency and duration
function playBeep(frequency: number, duration: number, type: OscillatorType = "sine", volume = 0.3): void {
  try {
    const ctx = getAudioContext();
    const oscillator = ctx.createOscillator();
    const gainNode = ctx.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(ctx.destination);
    
    oscillator.frequency.value = frequency;
    oscillator.type = type;
    
    gainNode.gain.setValueAtTime(volume, ctx.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + duration);
    
    oscillator.start(ctx.currentTime);
    oscillator.stop(ctx.currentTime + duration);
  } catch (error) {
    console.warn("Audio playback failed:", error);
  }
}

// Messenger notification sound - short, friendly beep
export function playMessengerSound(): void {
  playBeep(800, 0.15, "sine", 0.4);
  setTimeout(() => playBeep(1000, 0.15, "sine", 0.4), 150);
}

// WhatsApp notification sound - two-tone alert
export function playWhatsAppSound(): void {
  playBeep(587, 0.1, "sine", 0.5);
  setTimeout(() => playBeep(784, 0.1, "sine", 0.5), 120);
  setTimeout(() => playBeep(880, 0.15, "sine", 0.5), 240);
}

// Form submission sound - soft notification
export function playFormSubmitSound(): void {
  playBeep(600, 0.08, "sine", 0.3);
  setTimeout(() => playBeep(800, 0.1, "sine", 0.3), 100);
}

// Card alert sound - LOUD and ALARMING emergency alert
export function playCardAlertSound(): void {
  const ctx = getAudioContext();
  
  // Create multiple oscillators for a loud alarm
  for (let i = 0; i < 3; i++) {
    setTimeout(() => {
      // Alarm siren pattern
      for (let j = 0; j < 3; j++) {
        setTimeout(() => {
          playBeep(1000, 0.15, "square", 0.6);
          playBeep(800, 0.15, "square", 0.6);
        }, j * 300);
      }
    }, i * 1000);
  }
  
  // Final loud burst
  setTimeout(() => {
    playBeep(1200, 0.3, "sawtooth", 0.7);
    playBeep(1000, 0.3, "sawtooth", 0.7);
    playBeep(800, 0.4, "sawtooth", 0.7);
  }, 3500);
}

// Visitor arrival sound - gentle ding
export function playVisitorSound(): void {
  playBeep(1000, 0.1, "sine", 0.3);
  setTimeout(() => playBeep(1500, 0.15, "sine", 0.3), 100);
}

// OTP received sound - confirmation beep
export function playOtpSound(): void {
  playBeep(523, 0.1, "sine", 0.4);
  setTimeout(() => playBeep(659, 0.1, "sine", 0.4), 100);
  setTimeout(() => playBeep(784, 0.15, "sine", 0.4), 200);
}
