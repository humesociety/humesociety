/*
Set up interactive features of the availability form.
*/
const availability = document.querySelector('[name="user_availability"]')

if (availability) {
  const willingToReview = availability.querySelector('[name="user_availability[willingToReview]"]')

  const keywords = availability.querySelector('[name="user_availability[keywords]"]')

  const fixKeywordsRequired = () => {
    if (willingToReview.checked) {
      keywords.setAttribute('required', 'required')
    } else {
      keywords.removeAttribute('required')
    }
  }

  willingToReview.addEventListener('change', fixKeywordsRequired)

  fixKeywordsRequired()
}
