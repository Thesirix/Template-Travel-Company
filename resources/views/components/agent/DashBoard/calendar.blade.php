                   <div class="card-body">

                       <ul class="nav nav-tabs mb-3" id="calendarTabs" role="tablist">
                           <li class="nav-item">
                               <a class="nav-link active" id="calendar1-tab" data-toggle="tab" href="#calendar1"
                                   role="tab" aria-controls="calendar1" aria-selected="true">Emploi du temps</a>
                           </li>
                           <li class="nav-item">
                               <a class="nav-link" id="calendar2-tab" data-toggle="tab" href="#calendar2" role="tab"
                                   aria-controls="calendar2" aria-selected="false">Congés</a>
                           </li>
                       </ul>

                       <div class="tab-content">
                           <div class="tab-pane fade show active" id="calendar1" role="tabpanel"
                               aria-labelledby="calendar1-tab">
                               <div id="calendar-personal"></div>
                           </div>
                           <div class="tab-pane fade" id="calendar2" role="tabpanel" aria-labelledby="calendar2-tab">
                               <div id="calendar-team"></div>
                           </div>
                       </div>

                       {{-- ------------------------------------------------------systeme onglet-------------------- --}}


                       <div id='calendar'></div>
                   </div>
