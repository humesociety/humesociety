/*
Make some tables interactive - to show/hide various rows.
*/
// interactive elements
const filterSelects = Array.from(document.querySelectorAll('[data-filter]'))
const rangeSelects = Array.from(document.querySelectorAll('[data-range]'))
const includeSelects = Array.from(document.querySelectorAll('[data-include]'))

// actions
const filter = (e) => {
  const table = document.getElementById(e.currentTarget.dataset.table)
  const field = e.currentTarget.dataset.filter
  const rows = Array.from(table.querySelectorAll(`[data-${field}]`))
  const value = e.currentTarget.value
  rows.forEach((row) => {
    if (value === 'all' || row.dataset[field] === value) {
      row.classList.remove('collapsed')
    } else {
      row.classList.add('collapsed')
    }
  })
  table.classList.remove('loading')
  e.currentTarget.blur()
}

const range = (e) => {
  const table = document.getElementById(e.currentTarget.dataset.table)
  const bounds = e.currentTarget.dataset.range.split('-')
  const rows = Array.from(table.querySelectorAll(`[data-${bounds[0]}]`))
  const value = e.currentTarget.value
  rows.forEach((row) => {
    if (row.dataset[bounds[0]] <= value && value <= row.dataset[bounds[1]]) {
      row.classList.remove('hidden')
    } else {
      row.classList.add('hidden')
    }
  })
  table.classList.remove('loading')
  e.currentTarget.blur()
}

const include = (e) => {
  const table = document.getElementById(e.currentTarget.dataset.table)
  const field = e.currentTarget.dataset.include
  const rows = Array.from(table.querySelectorAll(`[data-${field}]`))
  const value = e.currentTarget.value
  rows.forEach((row) => {
    if (value === 'all' || row.dataset[field].split(', ').includes(value)) {
      row.classList.remove('collapsed')
    } else {
      row.classList.add('collapsed')
    }
  })
  table.classList.remove('loading')
  e.currentTarget.blur()
}

// bind actions to elements
filterSelects.forEach((x) => { x.addEventListener('change', filter) })
rangeSelects.forEach((x) => { x.addEventListener('change', range) })
includeSelects.forEach((x) => { x.addEventListener('change', include) })

// initialise page
filterSelects.forEach((x) => { x.dispatchEvent(new window.Event('change')) })
rangeSelects.forEach((x) => { x.dispatchEvent(new window.Event('change')) })
includeSelects.forEach((x) => { x.dispatchEvent(new window.Event('change')) })
