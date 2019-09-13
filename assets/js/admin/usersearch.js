const userForm = document.getElementById('user-form')
const userTotal = document.getElementById('user-total')
const userTable = document.getElementById('user-table')

const sort = (users) => {
  const sortBy = document.getElementById('sort')
  switch (sortBy.value) {
    case 'name':
      return users // already sorted by name on the server
    case 'joined':
      return users.sort((x, y) => x.dateJoined - y.dateJoined)
    case 'dues':
      return users.sort((x, y) => x.dues - y.dues)
  }
}

const filter = (users) => {
  const member = document.getElementById('type-member').checked
  const lapsed = document.getElementById('type-lapsed').checked
  const emailYes = document.getElementById('email-yes').checked
  const emailNo = document.getElementById('email-no').checked
  const hsYes = document.getElementById('hs-yes').checked
  const hsNo = document.getElementById('hs-no').checked
  const joined = document.getElementById('joined').value
  const dues = document.getElementById('dues').value
  users.forEach((user) => {
    user.dateJoined = new Date(user.dateJoined)
    user.dues = new Date(user.dues)
  })
  users = users.filter((user) => user.member)
  if (member) users = users.filter((user) => user.memberInGoodStanding)
  if (lapsed) users = users.filter((user) => user.memberInArrears)
  if (emailYes) users = users.filter((user) => user.receiveEmail)
  if (emailNo) users = users.filter((user) => !user.receiveEmail)
  if (hsYes) users = users.filter((user) => user.receiveHumeStudies)
  if (hsNo) users = users.filter((user) => !user.receiveHumeStudies)
  if (joined) users = users.filter((user) => user.dateJoined.getFullYear() === parseInt(joined))
  if (dues) {
    const month = parseInt(dues.split('/')[0])
    const year = parseInt(dues.split('/')[1])
    users = users.filter((user) => user.dues.getMonth() >= month && user.dues.getFullYear() >= year)
  }
  return users
}

const pad = int =>
  int < 10 ? `0${int.toString(10)}` : int.toString(10)

const userRow = (user, index) =>
  `<tr>
    <td>${index + 1}</td>
    <td>${user.lastname}, ${user.firstname}</td>
    <td>${user.email}</td>
    <td>${pad(user.dateJoined.getMonth() + 1)}/${pad(user.dateJoined.getDate())}/${user.dateJoined.getFullYear()}</td>
    <td>${pad(user.dues.getMonth() + 1)}/${user.dues.getFullYear()}</td>
    <td class="controls">
      <a href="/admin/society/member/view/${user.username}">Details</a>
    </td>
  </tr>`

const updateTable = (event) => {
  event.preventDefault()
  window.fetch('/data/users').then((response) => {
    response.json().then((data) => {
      const users = sort(filter(data))
      userTotal.innerHTML = users.length
      userTable.innerHTML = users.map(userRow).join('')
    })
  })
}

if (userForm && userTable) userForm.addEventListener('submit', updateTable)
