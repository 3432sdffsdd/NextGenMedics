/**
 * Client-side MCQ parser (mirrors backend QuizWordParserService).
 */

function reflowQuizText(text) {
  let t = text
  t = t.replace(/(?<=[.!?)\]"'0-9])\s+(\d+\.\s+)/g, '\n$1')
  t = t.replace(/(?<=[.!?)\]"'0-9])\s+(💡\s*Hint:)/gu, '\n$1')
  t = t.replace(/(?<=[.!?)\]"'0-9])\s+(Hint:)/gi, '\n$1')
  t = t.replace(/(?<=[.!?)\]"'0-9])\s+(Answer:\s*[A-Ea-e])/gi, '\n$1')
  t = t.replace(/(?<=[.!?)\]"'0-9])\s+(Rationale:)/gi, '\n$1')
  t = t.replace(/(?<=[a-z0-9)\]"'’])\s+([B-D])[.)]\s+/gu, '\n$1. ')
  t = t.replace(/(?<=[.!?)\]"'’])\s+(A)[.)]\s+/gu, '\n$1. ')
  return t
}

function normalizeQuizText(text) {
  let normalized = String(text || '').replace(/\r\n/g, '\n').replace(/\r/g, '\n').trim()
  normalized = normalized.replace(/^\uFEFF/, '').replace(/\u00A0/g, ' ')
  normalized = reflowQuizText(normalized)
  normalized = normalized.replace(/^\d+\s+questions\s*\n/im, '')
  const firstQ = normalized.match(/(\d+)\.\s+/m)
  if (firstQ && firstQ.index > 0) {
    normalized = normalized.slice(firstQ.index)
  }
  return normalized.trim()
}

function splitBlocks(text) {
  if (/\d+\.\s+/m.test(text)) {
    return text.split(/(?=^\d+\.\s+)/m).map((b) => b.trim()).filter(Boolean)
  }

  if (/Question\s+\d+\b/i.test(text)) {
    return text.split(/(?=Question\s+\d+\b)/i).map((b) => b.trim()).filter(Boolean)
  }

  const byBlank = text.split(/\n\s*\n+/).map((b) => b.trim()).filter(Boolean)
  if (byBlank.length > 1) return byBlank

  return text
    .split(/(?<=(?:^|\n)(?:ANSWER|Answer|Correct\s+Answer)\s*:\s*[A-Ea-e])\s*\n+/gim)
    .map((b) => b.trim())
    .filter(Boolean)
}

function extractOptions(body) {
  const options = {}
  const lineRegex = /^([A-Ea-e])[.)]\s*(.+)$/gm
  let m
  while ((m = lineRegex.exec(body)) !== null) {
    options[m[1].toUpperCase()] = m[2].trim()
  }
  if (Object.keys(options).length >= 4) return options

  const inlineRegex = /(?:^|\n|\s)([A-D])[.)]\s*(.*?)(?=(?:\s+[A-D][.)]\s)|(?:\s*(?:Answer|Rationale|ANSWER):)|$)/gis
  while ((m = inlineRegex.exec(body)) !== null) {
    const letter = m[1].toUpperCase()
    const optText = m[2].trim()
    if (optText) options[letter] = optText
  }
  return options
}

function extractQuestionText(body, optionLetters) {
  const questionLines = body.split('\n')
    .map((l) => l.trim())
    .filter(Boolean)
    .filter((line) => !/^Question\s+\d+/i.test(line))
    .filter((line) => !/^[A-Ea-e][.)]\s*/.test(line))
    .filter((line) => !/^(?:ANSWER|Answer|Correct\s+Answer)\s*:/i.test(line))
    .filter((line) => !/^(?:Rationale|Explanation)\s*:/i.test(line))

  let text = questionLines.join('\n').trim()
  if (!text && optionLetters.length) {
    const first = optionLetters[0]
    const m = body.match(new RegExp(`^(.*?)(?:\\s+${first}[.)]\\s)`, 'is'))
    if (m) text = m[1].replace(/^\d+\.\s*/, '').trim()
  }
  return text
}

function parseBlock(block, number) {
  const errors = []
  let explanation = null

  let body = block.replace(/^\d+\.\s*/, '')

  const rationaleMatch = body.match(/(?:Rationale|Explanation)\s*:\s*(.+?)(?=\n\s*\d+\.\s+|$)/is)
  if (rationaleMatch) {
    explanation = rationaleMatch[1].trim()
    body = body.replace(/(?:Rationale|Explanation)\s*:.+?(?=\n\s*\d+\.\s+|$)/is, '')
  }

  let correctLetter = null
  const correctMatch = body.match(/(?:ANSWER|Answer|Correct\s+Answer)\s*:\s*([A-Ea-e])\b/i)
  if (correctMatch) {
    correctLetter = correctMatch[1].toUpperCase()
    body = body.replace(/(?:ANSWER|Answer|Correct\s+Answer)\s*:\s*[A-Ea-e]\b[^\n]*/i, '')
  } else {
    errors.push('Missing answer (use Answer: B or ANSWER: B)')
  }

  const options = extractOptions(body)

  if (Object.keys(options).length < 4) {
    errors.push('Must have at least 4 options (A–D)')
  }

  const texts = Object.values(options)
  if (texts.length !== new Set(texts.map((t) => t.toLowerCase())).size) {
    errors.push('Duplicate option text detected')
  }

  if (correctLetter && !options[correctLetter]) {
    errors.push(`Answer ${correctLetter} does not match any option`)
  }

  const question_text = extractQuestionText(body, Object.keys(options))
  if (!question_text) errors.push('Missing question text')

  const optionRows = ['A', 'B', 'C', 'D', 'E']
    .filter((letter) => options[letter])
    .map((letter) => ({
      option_text: options[letter],
      is_correct: correctLetter === letter ? 1 : 0,
    }))

  return { number, question_text, explanation, options: optionRows, errors }
}

export function parseQuizWordText(text) {
  const normalized = normalizeQuizText(text)
  if (!normalized) {
    return { valid: [], invalid: [], summary: { total: 0, valid: 0, invalid: 0 } }
  }

  const blocks = splitBlocks(normalized)
  const valid = []
  const invalid = []

  blocks.forEach((block, i) => {
    const numMatch = block.match(/^(\d+)\.\s+/m) || block.match(/Question\s+(\d+)/i)
    const num = numMatch ? Number(numMatch[1]) : i + 1
    const parsed = parseBlock(block.trim(), num)
    if (parsed.errors.length) invalid.push(parsed)
    else valid.push(parsed)
  })

  return {
    valid,
    invalid,
    summary: { total: valid.length + invalid.length, valid: valid.length, invalid: invalid.length },
  }
}

/** @deprecated use downloadQuizTxtTemplate from quizFormat.js */
export { downloadQuizTxtTemplate as downloadQuizWordTemplate } from './quizFormat.js'
