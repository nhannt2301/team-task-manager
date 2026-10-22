/**
 * Format datetime (YYYY-MM-DD)
 */
export function formatDate(dateStr: string | null | undefined): string {
  if (!dateStr) return 'No due date'

  const date = new Date(dateStr)
  if (isNaN(date.getTime())) return dateStr

  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}