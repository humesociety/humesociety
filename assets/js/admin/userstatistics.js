const userStatistics = document.getElementById('user-statistics')

if (userStatistics) {
  window.fetch('/data/users').then((response) => {
    response.json().then((users) => {
      userStatistics.innerHTML = `<table class="statistics">
        <tr>
          <th>Members:</th>
          <td colspan="3" class="number">${users.filter(x => x.member).length}</td>
        </tr>
        <tr>
          <th>In good standing:</th>
          <td class="number">${users.filter(x => x.memberInGoodStanding).length}</td>
          <th>Lapsed:</th>
          <td class="number">${users.filter(x => x.memberInArrears).length}</td>
        </tr>
        <tr>
          <th>Accepting email:</th>
          <td class="number">${users.filter(x => x.receiveEmail).length}</td>
          <th>Declining email:</th>
          <td class="number">${users.filter(x => !x.receiveEmail).length}</td>
        </tr>
        <tr>
          <th>Accepting <i>Hume Studies</i>:</th>
          <td class="number">${users.filter(x => x.receiveHumeStudies).length}</td>
          <th>Declining <i>Hume Studies</i>:</th>
          <td class="number">${users.filter(x => !x.receiveHumeStudies).length}</td>
        </tr>
      </table>`
    })
  })
}
