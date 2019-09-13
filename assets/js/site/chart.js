/*
Membership statistics chart graphics (using Chart.js).
*/
import Chart from 'chart.js/dist/Chart.js'
import countries from './chart/countries.js'

// colours
const colours = ['#1f8dd6', '#5eb95e', '#f37b1d', '#8058a5', '#dd514c', '#fad232']

// show summary of data
const showSummary = (members) => {
  const countries = members.reduce((sofar, current) => {
    if (!sofar.find(x => x.country === current.country)) {
      sofar.push(current)
    }
    return sofar
  }, [])
  const institutions = members.reduce((sofar, current) => {
    if (!sofar.find(x => x.institution === current.institution)) {
      sofar.push(current)
    }
    return sofar
  }, [])
  summaryElement.innerHTML = `There are currently ${members.length} members of the Hume Society in good standing, from ${countries.length} different countries and ${institutions.length} different institutions.`
}

// generate chart for regions
const chartRegions = (canvas, members) => {
  const totals = regionTotals(members)
  const labels = totals.map(x => x.region)
  const data = totals.map(x => x.count)
  const chart = new Chart(canvas, {
    type: 'pie',
    data: { labels, datasets: [{ data, backgroundColor: colours }] }
  })
  return chart // just so my linter doesn't complain that chart isn't used
}

// calculate region totals (used by the function above)
const regionTotals = (members) => {
  const cTotals = countryTotals(members)
  const totals = []
  cTotals.forEach((total) => {
    try {
      const region = countries[total.country].region
      const existing = totals.find(x => x.region === region)
      if (existing) {
        existing.count += total.count
      } else {
        totals.push({ region, count: total.count })
      }
    } catch (error) {
      console.log(error)
      console.log(total)
    }
  })
  totals.sort((x, y) => y.count - x.count)
  return totals
}

// generate chart for top 5 countries
const chartCountriesTop5 = (canvas, members) => {
  const totals = countryTotals(members)
  const labels = totals.slice(0, 5).map(x => countries[x.country].name)
  const data = totals.slice(0, 5).map(x => x.count)
  labels.push('Other')
  data.push(totals.slice(5).reduce((sofar, current) => sofar + current.count, 0))
  const chart = new Chart(canvas, {
    type: 'pie',
    data: { labels, datasets: [{ data, backgroundColor: colours }] }
  })
  return chart // just so my linter doesn't complain that chart isn't used
}

// calculate country totals (used by the function above)
const countryTotals = (members) => {
  const totals = []
  members.forEach((member) => {
    if (member.country === null) member.country = 'NNN' // code for 'unknown' dummy country
    const existing = totals.find(x => x.country === member.country)
    if (existing) {
      existing.count += 1
    } else {
      totals.push({ country: member.country, count: 1 })
    }
  })
  totals.sort((x, y) => y.count - x.count)
  return totals
}

// grap dom elements
const summaryElement = document.getElementById('membership-summary')
const regionsCanvas = document.getElementById('membership-regions')
const countriesCanvas = document.getElementById('membership-countries')

// fetch data and render
if (summaryElement && regionsCanvas && countriesCanvas) {
  window.fetch('/data/members').then((response) => {
    response.json().then((members) => {
      showSummary(members)
      chartRegions(regionsCanvas, members)
      chartCountriesTop5(countriesCanvas, members)
    })
  })
}
