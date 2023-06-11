@php
    /** @var \App\Features\Shared\Configuration\BitPayConfiguration $configuration **/
    $donation = $configuration->getDonation();
    $denominations = $donation ->getDenominations();
    $maxPrice = $donation ->getMaxPrice();
    $encodedDenominations = json_encode($denominations, JSON_THROW_ON_ERROR);
@endphp

@extends('layouts.default')

@section('head_scripts')
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous"/>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
    <script type="text/javascript" src="{{ URL::asset ('js/invoices/donationForm.js') }}"></script>
    <script type="text/javascript">const donationForm = new DonationForm('{{$encodedDenominations}}', {{$maxPrice}})</script>

    <style type="text/tailwindcss">
        @layer components {
            .payment-wrapper {
                @apply w-full p-20
            }
            .payment-cell {
                @apply h-[50px] m-[5px] pt-2.5 bg-gray-300 text-black
            }
            .payment-info {
                @apply pt-2.5
            }
            .emailStyle {
                @apply w-9/10
            }
            .wrapper {
                @apply w-full mx-5
            }
            .customPayment {
                @apply w-[99%] h-[30px] text-black
            }
            .headerTop {
                color: #1C2445;
            }
            .headerBottom {
                color: #1C2445;
            }
            .donateButton {
                background-color: #1C2445 !important;
                border-color: #1C2445 !important;
            }
            .disclaimer {
                color: #1C2445;
                @apply mt-5 text-xs
            }
            .selectedPayment {
                background-color: {{$donation->getButtonSelectedBgColor()}};
                color: {{$donation->getButtonSelectedTextColor()}}
            }
            /**bitpay form css**/
            .bitpay-donate {
                @apply mt-5 mr-0
            }
            .bitpay-donate fieldset {
                @apply border-0
            }
            .bitpay-donate .field-input {
                @apply text-gray-600 bg-white border border-solid border-gray-300 h-[30px] box-border flex-grow
            }
            .bitpay-donate .field-input-wrapper {
                @apply inline-flex float-none w-4/5
            }
            .bitpay-donate input {
                @apply pr-[10px]
            }
            .bitpay-donate select {
                @apply pt-[3px] pr-[10px]
            }
            .bitpay-donate .bitpay-donate-button {
                @apply pt-[12px] pr-0 w-[188px] box-border
            }
            .bitpay-donate ul,
            .bitpay-donate li {
                @apply p-0 m-0 list-none
            }
            .bitpay-donate li {
                @apply pt-[10px] pr-0
            }
            .bitpay-donate-field {
                @apply clear-both w-2/5
            }
            .bitpay-donate-field label {
                @apply float-left w-[100px]
            }
            .bitpay-donate-field div {
                @apply float-left
            }
            .bitpay-donate-button-wrapper {
                @apply clear-both mx-auto text-center
            }
            input.bitpay-donate-error {
                @apply border-[2px] border-solid border-red-500
            }
        }

    </style>
@endsection

@section('content')

    @include('includes.navigation')

    <div class="container">
        @if($errorMessage !== null)
            <div class="mt-4 border-2 bg-red-700 text-white p-3">
                <span>{{$errorMessage}}</span>
            </div>
        @endif

        <div class="row payment-info">

            @foreach($denominations as $denomination)
                <div id="payment_{{$denomination}}" class="col-3 payment-cell payment"
                     onclick="donationForm.updateValue({{$denomination}})">
                    ${{$denomination}}
                </div>
            @endforeach
            @if($configuration->getDonation()->isEnableOther())
                <div class="col-3 payment-cell payment" id="payment_other">
                    <input step="0.01" type="number" class="customPayment" name="customPayment" id="customPayment"
                           onfocus="donationForm.updateCss(); donationForm.updateValue(this.value)"
                           placeholder="Other (Maximum ${{$maxPrice}})"
                           onchange="donationForm.updateValue(this.value)"/>
                </div>
            @endif

        </div>

        <form action="{{ route('createInvoice', [], false) }}" method="post" id="donateForm" class="bitpay-donate">
            <fieldset>
                <div class="row payment-info">
                    <div class="col-3">
                        <label>Name:</label>
                    </div>
                    <div class="col-9">
                        <input type="text" class="inputField  field-input bitpay-donate-field " name="buyerName"
                               id="buyerName" placeholder="Name" required="true"/>
                    </div>
                </div>
                <div class="row payment-info">
                    <div class="col-3">
                        <label>Address:</label>
                    </div>
                    <div class="col-9">
                        <input type="text" class="inputField  field-input bitpay-donate-field " name="buyerAddress1"
                               id="buyerAddress1" placeholder="Address" required="true"/>
                    </div>
                </div>
                <div class="row payment-info">
                    <div class="col-3">
                        <label for="buyerAddress2">Address 2:</label>
                    </div>
                    <div class="col-9">
                        <input type="text" class="inputField  field-input bitpay-donate-field " name="buyerAddress2"
                               id="buyerAddress2" placeholder="Address"/>
                    </div>
                </div>
                <div class="row payment-info">
                    <div class="col-3">
                        <label>City:</label>
                    </div>
                    <div class="col-9">
                        <input type="text" class="inputField  field-input bitpay-donate-field " name="buyerLocality"
                               id="buyerLocality" placeholder="City" required="true"/>
                    </div>
                </div>
                <div class="row payment-info">
                    <div class="col-3">
                        <label>State:</label>
                    </div>
                    <div class="col-9">
                        <select class="inputField bitpay-donate-field field-input" name="buyerRegion" id="buyerRegion"
                                required="true">
                            <option value="-" selected="selected">--Select a state--</option>
                            <option value="AL">Alabama</option>
                            <option value="AK">Alaska</option>
                            <option value="AZ">Arizona</option>
                            <option value="AR">Arkansas</option>
                            <option value="CA">California</option>
                            <option value="CO">Colorado</option>
                            <option value="CT">Connecticut</option>
                            <option value="DE">Delaware</option>
                            <option value="DC">District Of Columbia</option>
                            <option value="FL">Florida</option>
                            <option value="GA">Georgia</option>
                            <option value="HI">Hawaii</option>
                            <option value="ID">Idaho</option>
                            <option value="IL">Illinois</option>
                            <option value="IN">Indiana</option>
                            <option value="IA">Iowa</option>
                            <option value="KS">Kansas</option>
                            <option value="KY">Kentucky</option>
                            <option value="LA">Louisiana</option>
                            <option value="ME">Maine</option>
                            <option value="MD">Maryland</option>
                            <option value="MA">Massachusetts</option>
                            <option value="MI">Michigan</option>
                            <option value="MN">Minnesota</option>
                            <option value="MS">Mississippi</option>
                            <option value="MO">Missouri</option>
                            <option value="MT">Montana</option>
                            <option value="NE">Nebraska</option>
                            <option value="NV">Nevada</option>
                            <option value="NH">New Hampshire</option>
                            <option value="NJ">New Jersey</option>
                            <option value="NM">New Mexico</option>
                            <option value="NY">New York</option>
                            <option value="NC">North Carolina</option>
                            <option value="ND">North Dakota</option>
                            <option value="OH">Ohio</option>
                            <option value="OK">Oklahoma</option>
                            <option value="OR">Oregon</option>
                            <option value="PA">Pennsylvania</option>
                            <option value="RI">Rhode Island</option>
                            <option value="SC">South Carolina</option>
                            <option value="SD">South Dakota</option>
                            <option value="TN">Tennessee</option>
                            <option value="TX">Texas</option>
                            <option value="UT">Utah</option>
                            <option value="VT">Vermont</option>
                            <option value="VA">Virginia</option>
                            <option value="WA">Washington</option>
                            <option value="WV">West Virginia</option>
                            <option value="WI">Wisconsin</option>
                            <option value="WY">Wyoming</option>
                        </select>
                    </div>
                </div>
                <div class="row payment-info">
                    <div class="col-3">
                        <label>Zip:</label>
                    </div>
                    <div class="col-9">
                        <input type="text" class="inputField  field-input bitpay-donate-field " name="buyerPostalCode"
                               id="buyerPostalCode" placeholder="Zip Code" required="true"/>
                    </div>
                </div>
                <div class="row payment-info">
                    <div class="col-3">
                        <label>Phone Number:</label>
                    </div>
                    <div class="col-9">
                        <input type="text" class="inputField field-input bitpay-donate-field " name="buyerPhone"
                               id="buyerPhone" placeholder="Phone Number" required="true"/>
                    </div>
                </div>
                <div class="row payment-info">
                    <div class="col-3">
                        <label>Email:</label>
                    </div>
                    <div class="col-9">
                        <input type="email" class="inputField field-input bitpay-donate-field " name="buyerEmail"
                               id="buyerEmail" placeholder="Email address" autocapitalize="off"
                               autocorrect="off" required="true"/>
                    </div>
                </div>
                <input type="hidden" class="inputField field-input bitpay-donate-field " name="price" id="price"
                       placeholder="Amount" required="true"/>

                @foreach($configuration->getDesign()->getPosData()->getFields() as $field)
                    <th>
                        @switch($field->getType())
                            @case('select')
                                <div class="row payment-info">
                                    <div class="col-3">
                                        <label for="{{ $field->getName() }}">{{ $field->getLabel() }}</label>
                                    </div>
                                    <div class="col-9">
                                        <select id="{{ $field->getId() }}" name="{{ $field->getName() }}" required="{{ $field->isRequired() }}" class="bitpay-donate-field field-input">
                                            <option selected="selected" value=""></option>
                                            @foreach($field->getOptions() as $option)
                                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            @break

                            @case('fieldset')
                                <fieldset>
                                    <div class="row payment-info">
                                        <div class="col-12">
                                            <label>{{ $field->getLabel() }}</label>
                                        </div>
                                    </div>
                                    <div class="row payment-info">
                                        <fieldset class="col-12">
                                            @foreach($field->getOptions() as $option)
                                                <div class="col-3 payment-cell payment">
                                                    <input type="radio" required="{{ $field->isRequired() }}" id="{{ $option['id'] }}"
                                                           name="{{ $field->getName() }}" value="{{ $option['value'] }}" />
                                                    <label for="{{ $option['id'] }}">{{ $option['label'] }}</label>
                                                </div>
                                            @endforeach
                                        </fieldset>
                                    </div>
                                </fieldset>
                            @break

                            @case('text')
                                <div class="row payment-info">
                                    <div class="col-3">
                                        <label for="{{ $field->getName() }}">{{ $field->getLabel() }}</label>
                                    </div>
                                    <div class="col-9">
                                        <input type="text" id="{{ $field->getId() }}" name="{{ $field->getName() }}"
                                               required="{{ $field->isRequired() }}"
                                               class="inputField field-input bitpay-donate-field" />
                                    </div>
                                </div>
                            @break

                            @case('price')
                                <div class="row payment-info">
                                    <div class="col-3">
                                        <label for="{{ $field->getName() }}">{{ $field->getLabel() }}</label>
                                    </div>
                                    <div class="col-9">
                                        <div class="absolute inset-y-0 left-2 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm"> {{ $field->getCurrency() }} </span>
                                        </div>
                                        <input type="number" step=".01" id="{{ $field->getId() }}"
                                               name="{{ $field->getName() }}" required="{{ $field->isRequired() }}"
                                               class="ml-5 focus:ring-indigo-500 focus:border-indigo-500 block border-gray-300" placeholder="0.00" attr="aria-describedby={{ $field->getId() }}-currency" />
                                    </div>
                                </div>
                            @break

                        @endswitch

                    </th>

                @endforeach

            </fieldset>
        </form>

        <div class="row payment-info">
            <div class="col-12">
                <p class="disclaimer">{{$donation->getFooterText()}}</p>
            </div>
        </div>

        <div class="bitpay-donate-button-wrapper">
            <input class="bitpay-donate-button" onclick="donationForm.submitForm();return false;"
                   src="https://bitpay.com/cdn/merchant-resources/pay-with-bitpay-card-group.svg"
                   onerror="this.onerror=null; this.src='https://test.bitpay.com/cdn/en_US/bp-btn-donate-currencies.svg'"
                   type="image" alt="BitPay, the easy way to pay with bitcoins."/>
        </div>

    </div>

    <script type="text/javascript" src="{{ URL::asset ('js/invoices/invoiceSnackBar.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset ('js/invoices/updateInvoiceFromInvoiceForm.js') }}"></script>
    <script type="text/javascript">new UpdateInvoiceFromInvoiceForm('{{$sseUrl}}', '{{$sseTopic}}').execute()</script>

@stop
