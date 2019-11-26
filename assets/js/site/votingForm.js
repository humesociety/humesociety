/*
Voting form validation.
*/
const votingForm = document.querySelector('[name="voting_type"]')
const ordinaryDiv = document.querySelector('[data-ordinary]')

if (votingForm && ordinaryDiv) {
  const ordinaryLimit = parseInt(ordinaryDiv.dataset.ordinary)
  let ordinaryCount = 0
  let presidentCount = 0
  let evptCount = 0

  const ordinaryCheckboxes = Array.from(votingForm.querySelectorAll('checkbox[data-ordinary]'))
  const presidentCheckboxes = Array.from(votingForm.querySelectorAll('checkbox[data-president]'))
  const evptCheckboxes = Array.from(votingForm.querySelectorAll('checkbox[data-evpt]'))

  ordinaryCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener('change', (e) => {
      if (checkbox.checked) {
        ordinaryCount += 1
      } else {
        ordinaryCount -= 1
      }
      if (ordinaryCount === ordinaryLimit) {
        ordinaryCheckboxes.forEach((checkbox) => {
          if (!checkbox.checked) checkbox.setAttribute('disabled', 'disabled')
        })
      } else {
        ordinaryCheckboxes.forEach((checkbox) => {
          checkbox.removeAttribute('disabled')
        })
      }
    })
  })

  presidentCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener('change', (e) => {
      if (checkbox.checked) {
        presidentCount += 1
      } else {
        presidentCount -= 1
      }
      if (presidentCount === 1) {
        presidentCheckboxes.forEach((checkbox) => {
          if (!checkbox.checked) checkbox.setAttribute('disabled', 'disabled')
        })
      } else {
        presidentCheckboxes.forEach((checkbox) => {
          checkbox.removeAttribute('disabled')
        })
      }
    })
  })

  evptCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener('change', (e) => {
      if (checkbox.checked) {
        evptCount += 1
      } else {
        evptCount -= 1
      }
      if (evptCount === 1) {
        evptCheckboxes.forEach((checkbox) => {
          if (!checkbox.checked) checkbox.setAttribute('disabled', 'disabled')
        })
      } else {
        evptCheckboxes.forEach((checkbox) => {
          checkbox.removeAttribute('disabled')
        })
      }
    })
  })
}
