/*
Hume Studies issues archive.
*/
// interactive dom elements
const browse = document.getElementById('browse-archive')
const search = document.getElementById('search-archive')
const searchAuthors = document.getElementById('search-authors')
const searchTitles = document.getElementById('search-titles')
const searchResults = document.getElementById('search-results')

// variable for storing the archive data
const archive = {}

// function for preparing the archive data for use here (based on what's fetched from the server)
const prepareArchive = (user, issues) => {
  // add reference to isse to each article
  issues.forEach((issue) => { issue.articles.forEach((article) => { article.issue = issue }) })
  // store issues and articles in the archive global
  archive.issues = issues
  archive.articles = issues.reduce((sofar, current) => sofar.concat(current.articles), [])
  // create volumes and store in the archive global
  archive.volumes = issues.reduce((sofar, current) => {
    if (sofar[current.volume - 1]) {
      sofar[current.volume - 1].unshift(current)
    } else {
      sofar.push([current])
    }
    return sofar
  }, []).reverse()
  // maybe set issues from five most recent volumes as locked (depending on user)
  if (user === null || !user.memberInGoodStanding) {
    for (let i = 0; i < 5; i += 1) {
      archive.volumes[i].forEach((issue) => { issue.locked = true })
    }
  }
  // debugging
  window.archive = archive
}

// simple function for creating a dom element (with a class and innerHTML)
const create = (type, _class = null, innerHTML = null) => {
  const element = document.createElement(type)
  if (_class) element.classList.add(_class)
  if (innerHTML) element.innerHTML = innerHTML
  return element
}

// display issue title
const displayIssueTitle = (issue) => {
  return `Volume ${issue.volume}, Number ${issue.number || '1&amp;2'} (${issue.month} ${issue.year})`
}

// display article pages
const displayArticlePages = (article) => {
  if (article.startPage) {
    if (article.endPage) return `pp. ${article.startPage}-${article.endPage}`
    return `p. ${article.startPage}`
  }
}

const displayArticleContext = (article) => {
  if (article.startPage) return `${displayIssueTitle(article.issue)}, ${displayArticlePages(article)}`
  return displayIssueTitle(article.issue)
}

// display an article
const displayArticle = (article, search = null) => {
  const archivePDF = article =>
    article.issue.locked
      ? '<span class="archive-link" title="access restricted to members in good standing"><i class="fas fa-lock"></i> Archive PDF</span>'
      : `<a href="/uploads/issues/v${article.issue.volume}n${article.issue.number}/${article.filename}" class="archive-link" target="blank"><i class="fas fa-lock-open"></i> Archive PDF</a>`
  const title = (search && search.title)
    ? article.title.replace(search.title, '<mark>$&</mark>')
    : article.title
  const authors = (search && search.author)
    ? article.authors.replace(search.author, '<mark>$&</mark>')
    : article.authors
  const div = create('div', 'article')
  if (article.museId) {
    div.innerHTML += `<div class="title"><h5><a href="https://muse.jhu.edu/article/${article.museId}" target="blank">${title}</a></h5>${archivePDF(article)}</div>`
  } else {
    div.innerHTML += `<div class="title"><h5>${title}</h5>${archivePDF(article)}</div>`
  }
  if (authors) div.innerHTML += `<p class="author">${authors}</p>`
  div.innerHTML += `<p class="pages">${displayArticleContext(article)}</p>`
  if (article.doi) div.innerHTML += `<p class="doi">DOI: <a href="${article.url}" target="blank">${article.doi}</a></p>`
  return div
}

// display an issue
const browseIssue = (issue) => {
  const index = archive.issues.indexOf(issue)
  const nav = create('nav', 'context')
  const all = create('a', null, 'all issues')
  const prev = create('a', null)
  const next = create('a', null)
  const h4 = create('h4', null, `<a href="https://muse.jhu.edu/issue/${issue.museId}">${displayIssueTitle(issue)}</a>`)
  const h5 = create('p', null, `<em>edited by ${issue.editors}</em>`)
  all.addEventListener('click', (e) => { browseAll() })
  if (archive.issues[index - 1]) {
    prev.innerHTML = '&lt; previous issue'
    prev.addEventListener('click', (e) => { browseIssue(archive.issues[index - 1]) })
  }
  if (archive.issues[index + 1]) {
    next.innerHTML = 'next issue &gt;'
    next.addEventListener('click', (e) => { browseIssue(archive.issues[index + 1]) })
  }
  window.scrollTo(0, 0)
  browse.innerHTML = ''
  nav.appendChild(prev)
  nav.appendChild(all)
  nav.appendChild(next)
  browse.appendChild(nav)
  browse.appendChild(h4)
  browse.appendChild(h5)
  issue.articles.forEach((article) => { browse.appendChild(displayArticle(article)) })
}

// display all issues for browsing
const browseAll = () => {
  const displayIssue = (issue) => {
    const li = create('li')
    const a = create('a', null, `Number ${issue.number || '1&amp;2'}, ${issue.month}`)
    a.addEventListener('click', (e) => { browseIssue(issue) })
    li.appendChild(a)
    return li
  }
  const displayVolume = (volume) => {
    const div = create('div', 'volume')
    const h4 = create('h4', 'title', `Volume ${volume[0].volume}, ${volume[0].year}`)
    const ul = create('ul', 'issues')
    div.appendChild(h4)
    div.appendChild(ul)
    volume.forEach((issue) => { ul.appendChild(displayIssue(issue)) })
    return div
  }
  window.scrollTo(0, 0)
  browse.innerHTML = ''
  archive.volumes.forEach((volume) => { browse.appendChild(displayVolume(volume)) })
}

// author search function
const authorSearch = (text) => {
  const regex = new RegExp(text, 'i')
  console.log(regex)
  const hits = text.length > 1
    ? archive.articles.filter(article => article.authors && article.authors.match(regex))
    : []
  const nounVerb = hits.length === 1 ? 'article matches' : 'articles match'
  searchResults.innerHTML = `<p>${hits.length} ${nounVerb} your search criteria.</p>`
  hits.forEach((hit) => { searchResults.appendChild(displayArticle(hit, { author: regex })) })
}

// title search function
const titleSearch = (text) => {
  const regex = new RegExp(text, 'i')
  const hits = text.length > 1
    ? archive.articles.filter(article => article.title.match(regex))
    : []
  const nounVerb = hits.length === 1 ? 'article matches' : 'articles match'
  searchResults.innerHTML = `<p>${hits.length} ${nounVerb} your search criteria.</p>`
  hits.forEach((hit) => { searchResults.appendChild(displayArticle(hit, { title: regex })) })
}

// set up the archive
if (browse && search) {
  window.fetch('/data/user').then((response) => {
    response.json().then((user) => {
      window.fetch('/data/issues').then((response) => {
        response.json().then((issues) => {
          // initialise the archive data
          prepareArchive(user, issues)
          // show all issues for browsing
          browseAll()
          // setup search boxes
          searchAuthors.addEventListener('keyup', (e) => { authorSearch(e.currentTarget.value) })
          searchTitles.addEventListener('keyup', (e) => { titleSearch(e.currentTarget.value) })
        })
      })
    })
  })
}
