/* globals paypal */
/*
Paypal integration for the membership subscription payment buttons.
*/
const paypalButtons = document.getElementById('paypal-buttons')
const duration = document.getElementById('duration')
const regular = document.getElementById('regular')
const student = document.getElementById('student')
const total = document.getElementById('total')

const getValue = () => {
  if (regular.checked) {
    if (duration.value === '1') return '35'
    if (duration.value === '2') return '52.50'
    if (duration.value === '5') return '150'
  } else {
    if (duration.value === '1') return '25'
    if (duration.value === '2') return '37.50'
  }
}

const getDescription = () => {
  if (regular.checked) {
    if (duration.value === '1') return 'Regular Membership (1 year)'
    if (duration.value === '2') return 'Regular Membership (2 years)'
    if (duration.value === '5') return 'Regular Membership (5 years)'
  } else {
    if (duration.value === '1') return 'Student Membership (1 year)'
    if (duration.value === '2') return 'Student Membership (2 years)'
  }
}

const createOrder = (data, actions) =>
  actions.order.create({
    purchase_units: [
      { amount: { value: getValue() }, description: getDescription() }
    ]
  })

const onApprove = (data, actions) =>
  actions.order.capture().then((details) => {
    window.location.pathname = `/account/paid/${data.orderID}`
  })

const updatePage = () => {
  if (student.checked) {
    if (duration.value === '5') duration.value = '2'
    duration.children[2].setAttribute('disabled', 'disabled')
  } else {
    duration.children[2].removeAttribute('disabled')
  }
  total.innerHTML = `$${getValue()}`
}

if (paypalButtons) {
  paypal.Buttons({ createOrder, onApprove }).render('#paypal-buttons')
  duration.addEventListener('change', updatePage)
  regular.addEventListener('change', updatePage)
  student.addEventListener('change', updatePage)
}
