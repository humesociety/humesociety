/*
Fill form fields with user details based on linked user dropdown selection.
*/
const selects = document.querySelectorAll('[data-action="fill-user-details"]')

selects.forEach((select) => {
  select.addEventListener('change', (e) => {
    const name = select.dataset.form
    const form = document.querySelector(`form[name="${name}"]`)
    const firstname = form.querySelector(`[name="${name}[firstname]"]`)
    const lastname = form.querySelector(`[name="${name}[lastname]"]`)
    const email = form.querySelector(`[name="${name}[email]"]`)
    const institution = form.querySelector(`[name="${name}[institution]"]`)
    const keywords = form.querySelector(`[name="${name}[keywords]"]`)
    if (select.value) {
      window.fetch(`/data/user/${select.value}`).then((response) => {
        response.json().then((user) => {
          if (firstname && user.firstname) {
            firstname.value = user.firstname
            firstname.setAttribute('readonly', true)
          }
          if (lastname && user.lastname) {
            lastname.value = user.lastname
            lastname.setAttribute('readonly', true)
          }
          if (email && user.email) {
            email.value = user.email
            email.setAttribute('readonly', true)
          }
          if (institution && user.institution) {
            institution.value = user.institution
            institution.setAttribute('readonly', true)
          }
          if (keywords && user.keywords) {
            keywords.value = user.keywords
            keywords.setAttribute('readonly', true)
          }
        })
      })
    } else {
      if (firstname) {
        firstname.value = ''
        firstname.removeAttribute('readonly')
      }
      if (lastname) {
        lastname.value = ''
        lastname.removeAttribute('readonly')
      }
      if (email) {
        email.value = ''
        email.removeAttribute('readonly')
      }
      if (institution) {
        institution.value = ''
        institution.removeAttribute('readonly')
      }
      if (keywords) {
        keywords.value = ''
        keywords.removeAttribute('readonly')
      }
    }
  })
})
