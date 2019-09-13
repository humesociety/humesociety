/*
Display table of members. At the moment this needn't be done with JavaScript, but in due course I
will want to make the table interactive (searchable and orderable).
*/
const membershipList = document.getElementById('membership-list')

const memberTableRow = (member, index) =>
  `<tr>
    <td>${index + 1}</td>
    <td>${member.firstname} ${member.lastname}</td>
    <td>${member.institution || '-'}</td>
    <td>${member.country}</td>
  </tr>`

if (membershipList) {
  window.fetch('/data/members').then((response) => {
    response.json().then((members) => {
      membershipList.innerHTML = members.map(memberTableRow).join('')
    })
  })
}
