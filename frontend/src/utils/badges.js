const EMOJI = {
  flame: '🔥',
  crown: '👑',
  target: '🎯',
  trophy: '🏆',
  zap: '⚡',
  medal: '🏅',
}

export function badgeEmoji(icon) {
  return EMOJI[icon] || '🏅'
}
