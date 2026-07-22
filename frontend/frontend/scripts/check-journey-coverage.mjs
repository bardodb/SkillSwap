#!/usr/bin/env node
/**
 * Parses the YAML block in docs/testing/e2e-journey-matrix.md
 * Fails if covered / (P0+P1 non-excluded) < 0.90
 */
import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const matrixPath = path.resolve(__dirname, '../../../docs/testing/e2e-journey-matrix.md')
const MIN_RATIO = 0.9

function parseScenarios(md) {
  const match = md.match(/```yaml\r?\n([\s\S]*?)\r?\n```/)
  if (!match) throw new Error('No yaml block found in journey matrix')
  const yaml = match[1]
  const scenarios = []
  let current = null
  for (const rawLine of yaml.split(/\r?\n/)) {
    const line = rawLine.replace(/\t/g, '  ')
    const idMatch = line.match(/^\s+- id:\s*(.+)\s*$/)
    if (idMatch) {
      if (current) scenarios.push(current)
      current = { id: idMatch[1].trim() }
      continue
    }
    if (!current) continue
    const kv = line.match(/^\s+(domain|title|type|priority|status|spec):\s*(.+)\s*$/)
    if (kv) current[kv[1]] = kv[2].trim()
  }
  if (current) scenarios.push(current)
  return scenarios
}

const md = fs.readFileSync(matrixPath, 'utf8')
const scenarios = parseScenarios(md)
const eligible = scenarios.filter(
  (s) => (s.priority === 'P0' || s.priority === 'P1') && s.status !== 'excluded'
)
const covered = eligible.filter((s) => s.status === 'covered')
const ratio = eligible.length === 0 ? 0 : covered.length / eligible.length

console.log(`Journey coverage: ${covered.length}/${eligible.length} (${(ratio * 100).toFixed(1)}%)`)
console.log(`Minimum required: ${(MIN_RATIO * 100).toFixed(0)}% (≥${Math.ceil(eligible.length * MIN_RATIO)})`)

if (ratio < MIN_RATIO) {
  const pending = eligible.filter((s) => s.status !== 'covered').map((s) => s.id)
  console.error('FAIL: journey coverage below threshold')
  console.error('Pending:', pending.join(', '))
  process.exit(1)
}

console.log('PASS: journey coverage gate')
process.exit(0)
